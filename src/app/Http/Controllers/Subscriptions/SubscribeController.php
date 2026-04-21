<?php

namespace App\Http\Controllers\Subscriptions;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Event;

use App\Http\Controllers\Controller;

class SubscribeController extends Controller
{
    public function showSubscribeForm(Request $request)
    {
        $eventId = $request->route('event_id');
        $event = Event::with(['modalities', 'kits'])->findOrFail($eventId);

        return view('subscriptions.subscribe', compact('event'));
    }

    public function mySubscriptions(Request $request)
    {
        // Busca as inscrições do usuário logado, filtrar por organizador e carrega a relação do evento
        $subscriptions = Subscription::with(['event', 'modality', 'kit'])
            ->where('user_id', auth()->id())
            ->whereHas('event', function ($query) use ($request) {
                $query->where('organizer_id', $request->current_organizer_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // dd($subscriptions); // Debug: Verificar os dados retornados

        return view('subscriptions.my', compact('subscriptions'));
    }

    public function subscribe(Request $request)
    {
        // Valida se o usuário preencheu a modalidade e o kit
        $request->validate([
            'modality_id' => 'required',
            'kit_id'      => 'required',
        ], [
            'required' => 'Por favor, selecione as opções de modalidade e kit.'
        ]);

        $modalityInput = $request->input('modality_id');
        $kitInput      = $request->input('kit_id');

        $eventId = $request->route('event_id');

        // Valida se o evento realmente existe no banco antes de criar a inscrição.
        // Se não existir, retorna um erro 404 automaticamente.
        $event = Event::findOrFail($eventId);

        // Busca a inscrição existente para este usuário neste evento
        $existingSubscription = Subscription::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingSubscription) {
            // Se já existe e NÃO está cancelada, avisa que já está inscrito
            if ($existingSubscription->status !== 'canceled') {
                return redirect('/my-subscriptions')->with([
                    'modal_type'  => 'info',
                    'user_name'   => auth()->user()->name,
                    'event_title' => $event->title,
                ]);
            }

            // Se estava cancelada, reativamos ela (Reaproveita a linha e evita erro de Unique no DB!)
            $existingSubscription->update([
                'modality_id' => $modalityInput,
                'kit_id'      => $kitInput,
                'price'       => 0.05, // Atualize futuramente se os kits tiverem preços variados
                'status'      => 'pending',
                'bib_number'  => null,
            ]);
        } else {
            // Se não encontrou nenhuma inscrição anterior, cria uma nova
            Subscription::create([
                'event_id'    => $event->id,
                'user_id'     => auth()->id(),
                'modality_id' => $modalityInput,
                'kit_id'      => $kitInput,
                'price'       => 0.05,
                'status'      => 'pending',
                'bib_number'  => null,
            ]);
        }

        return redirect('/my-subscriptions')->with([
            'modal_type'  => 'success',
            'user_name'   => auth()->user()->name,
            'event_title' => $event->title,
        ]);
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|integer',
        ]);

        $subscription = Subscription::where('id', $request->subscription_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Só permite o cancelamento se a inscrição ainda estiver pendente de pagamento
        if ($subscription->status === 'pending') {
            $subscription->update(['status' => 'canceled']);
            return redirect()->back()->with('success', 'Sua inscrição foi cancelada com sucesso.');
        }

        return redirect()->back()->with('error', 'Apenas inscrições pendentes podem ser canceladas.');
    }
}
