<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use App\Models\Subscription;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PixController extends Controller
{
    public function generatePix(Request $request)
    {

        $subscriptionId = $request->subscription_id;

        $subscription = Subscription::find($subscriptionId);

        // Cobra o preço que a inscrição registrou — que é o preço do kit escolhido
        // no momento em que ela foi criada (SubscribeController). Não existe mais
        // sobreposição global de valor: os eventos de teste têm R$ 0,05 gravado
        // como preço real do kit, e os cadastrados pelo painel têm o preço deles.
        $pix = MercadoPagoService::createPixPayment(
            (float) $subscription->price,
            auth()->user()->email,
            $subscriptionId // Enviando o ID da inscrição como referência externa
        );

        if (!$pix) {
            return redirect('/my-subscriptions')->withErrors([
                'pix' => 'Estamos com instabilidade no pagamento no momento. Tente novamente mais tarde.',
            ]);
        }

        Payment::create([
            'subscription_id' => $subscriptionId,
            'provider' => 'mercadopago',
            'payment_method' => 'pix',
            'status' => 'pending',
            'transaction_id' => $pix->id,
            'qr_code' => $pix->point_of_interaction->transaction_data->qr_code,
            'qr_code_base64' => $pix->point_of_interaction->transaction_data->qr_code_base64,
            'ticket_url' => $pix->point_of_interaction->transaction_data->ticket_url,
            'expires_at' => !empty($pix->date_of_expiration) ? Carbon::parse($pix->date_of_expiration)->format('Y-m-d H:i:s') : null,
            'payload' => json_encode($pix)
        ]);

        return view('subscriptions.generate-pix', compact('pix', 'subscriptionId'));

    }

    // Retorna apenas o status da inscrição para o Javascript via API
    public function checkStatus($id)
    {
        $subscription = Subscription::find($id);
        return response()->json(['status' => $subscription ? $subscription->status : 'pending']);
    }

    // Carrega a tela de Sucesso
    public function success($id)
    {
        $subscription = Subscription::findOrFail($id);
        return view('subscriptions.success', compact('subscription'));
    }
}
