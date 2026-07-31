<?php

namespace App\Http\Controllers\Subscriptions;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        $eventId = $request->route('event_id');

        // Valida se o evento realmente existe no banco antes de criar a inscrição.
        // Se não existir, retorna um erro 404 automaticamente.
        $event = Event::findOrFail($eventId);

        // Valida que a modalidade e o kit foram preenchidos e que de fato pertencem a este evento
        $request->validate([
            'modality_id' => ['required', 'integer', Rule::exists('event_modalities', 'id')->where('event_id', $event->id)],
            'kit_id'      => ['required', 'integer', Rule::exists('event_kits', 'id')->where('event_id', $event->id)],
        ], [
            'required' => 'Por favor, selecione as opções de modalidade e kit.',
            'exists'   => 'A modalidade ou o kit selecionado não é válido para este evento.',
        ]);

        $modalityInput = $request->input('modality_id');
        $kitInput      = $request->input('kit_id');

        // Busca a inscrição existente para este usuário neste evento.
        // Cancelar uma inscrição apaga a linha (ver cancel()), então uma inscrição
        // encontrada aqui só pode estar pending ou paid — nunca cancelled.
        $existingSubscription = Subscription::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingSubscription) {
            return redirect('/my-subscriptions')->with([
                'modal_type'  => 'info',
                'user_name'   => auth()->user()->name,
                'event_title' => $event->title,
            ]);
        }

        Subscription::create([
            'event_id'    => $event->id,
            'user_id'     => auth()->id(),
            'modality_id' => $modalityInput,
            'kit_id'      => $kitInput,
            'price'       => 0.05,
            'status'      => 'pending',
            'bib_number'  => null,
        ]);

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

        $subscription = Subscription::with('event')->where('id', $request->subscription_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Guarda o título do evento para exibir no modal após a exclusão
        $eventTitle = $subscription->event->title ?? 'Evento';

        // Só permite o cancelamento se a inscrição ainda estiver pendente de pagamento
        if ($subscription->status === 'pending') {
            
            // Apaga possíveis registros de pagamento pendentes atrelados a esta inscrição para não gerar lixo na base
            if (class_exists(\App\Models\Payment::class)) {
                \App\Models\Payment::where('subscription_id', $subscription->id)->delete();
            }

            // Apaga fisicamente o registro de inscrição
            $subscription->delete();

            return redirect()->back()->with([
                'modal_type'  => 'cancel',
                'user_name'   => auth()->user()->name,
                'event_title' => $eventTitle,
            ]);
        }

        return redirect()->back()->with('error', 'Apenas inscrições pendentes podem ser canceladas.');
    }
}
