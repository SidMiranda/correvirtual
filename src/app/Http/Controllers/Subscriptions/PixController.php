<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;

class PixController extends Controller
{
    public function generatePix(Request $request)
    {

        $subscriptionId = $request->subscription_id;

        $subscription = Subscription::find($subscriptionId);

        $pix = MercadoPagoService::createPixPayment(
            (float) $subscription->price,
            auth()->user()->email,
            $subscriptionId // Enviando o ID da inscrição como referência externa
        );

        Payment::create([
            'subscription_id' => $subscriptionId,
            'provider' => 'mercadopago',
            'payment_method' => 'pix',
            'status' => 'pending',
            'transaction_id' => $pix->id,
            'qr_code' => $pix->point_of_interaction->transaction_data->qr_code,
            'qr_code_base64' => $pix->point_of_interaction->transaction_data->qr_code_base64,
            'ticket_url' => $pix->point_of_interaction->transaction_data->ticket_url,
            'expires_at' => $pix->date_of_expiration,
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
