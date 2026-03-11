<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Payment;
use App\Services\MercadoPagoService;

class RegistrationController extends Controller
{

    public function pay($registrationId)
    {

        $registration = Registration::findOrFail($registrationId);

        // verifica se já existe pagamento pendente
        $payment = Payment::where('registration_id', $registration->id)
            ->where('status', 'pending')
            ->first();

        if ($payment) {

            return view('teste-pix', [
                'pix' => (object)[
                    'id' => $payment->transaction_id,
                    'point_of_interaction' => (object)[
                        'transaction_data' => (object)[
                            'qr_code' => $payment->qr_code,
                            'qr_code_base64' => $payment->qr_code_base64,
                            'ticket_url' => $payment->ticket_url
                        ]
                    ]
                ]
            ]);
        }

        // gera novo PIX
        $pix = MercadoPagoService::createPixPayment(
            10.00,
            auth()->user()->email
        );

        // salva pagamento
        Payment::create([
            'registration_id' => $registration->id,
            'transaction_id' => $pix->id,
            'qr_code' => $pix->point_of_interaction->transaction_data->qr_code,
            'qr_code_base64' => $pix->point_of_interaction->transaction_data->qr_code_base64,
            'ticket_url' => $pix->point_of_interaction->transaction_data->ticket_url,
            'expires_at' => $pix->date_of_expiration,
            'payload' => json_encode($pix)
        ]);

        return view('teste-pix', compact('pix'));
    }
}
