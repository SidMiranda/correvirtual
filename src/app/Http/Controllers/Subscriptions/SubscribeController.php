<?php

namespace App\Http\Controllers\Subscriptions;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Event;

use App\Http\Controllers\Controller;

class SubscribeController extends Controller
{
    public function showSubscribeForm()
    {
        return view('subscriptions.subscribe');
    }

    public function mySubscriptions()
    {
        return view('subscriptions.my');
    }

    public function subscribe(Request $request)
    {
        $modalityInput = $request->input('modality_id');
        $kitInput      = $request->input('kit_id');

        $eventId = $request->route('event_id');

        // Valida se o evento realmente existe no banco antes de criar a inscrição.
        // Se não existir, retorna um erro 404 automaticamente.
        $event = Event::findOrFail($eventId);

        // Verifica se o usuário já está inscrito neste evento
        $alreadySubscribed = Subscription::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadySubscribed) {
            return redirect('/my-subscriptions')->with('info', 'Você já está inscrito neste evento.');
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

        return redirect('/my-subscriptions')->with('success', 'Inscrição realizada com sucesso! Aguardando pagamento.');
    }
}
