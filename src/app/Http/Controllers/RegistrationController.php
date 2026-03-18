<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Payment;
use App\Models\Event;
use App\Models\EventKit;
use App\Models\EventModality;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function register(){
        return view('registrations.create');
    }
    
    public function store(Request $request, $eventId)
    {
        $modalityInput = $request->input('modality_id');
        $kitInput      = $request->input('kit_id');

        Registration::create([
            'event_id'    => $eventId,
            'user_id'     => auth()->id(),
            'modality_id' => $modalityInput,
            'kit_id'      => $kitInput,
            'price'       => 2.00,
            'status'      => 'pending',
            'bib_number'  => null,
        ]);

        return redirect('/')->with('success', 'Inscrição realizada com sucesso! Aguardando pagamento.');
    }

    public function pay($registrationId)
    {
        $registration = Registration::findOrFail($registrationId);
        
        // Segurança: O usuário logado é dono dessa inscrição?
        if ($registration->user_id !== auth()->id()) {
            abort(403);
        }

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
            (float) $registration->price, // Corrigido para usar o preço da inscrição
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

    public function create($id)
    {
        $event = Event::findOrFail($id);
        return view('registrations.create', compact('event'));
    }

    public function myRegistrations()
    {
        $user = auth()->user();

        $registrations = Registration::with(['event', 'modality', 'kit'])
            ->where('user_id', $user->id)
            ->get();
        return view('registrations.my', compact('registrations'));
    }
}

    // 4. Gerar o PIX no Mercado Pago
    // Agora usamos o valor real ($registration->price) e não mais 10.00 fixo
    // $pix = MercadoPagoService::createPixPayment(
    //     (float) $registration->price,
    //     auth()->user()->email
    // );

    // 5. Salvar o registro do Pagamento
    // Payment::create([
    //     'registration_id' => $registration->id,
    //     'transaction_id'  => $pix->id,
    //     'qr_code'         => $pix->point_of_interaction->transaction_data->qr_code,
    //     'qr_code_base64'  => $pix->point_of_interaction->transaction_data->qr_code_base64,
    //     'ticket_url'      => $pix->point_of_interaction->transaction_data->ticket_url,
    //     'expires_at'      => $pix->date_of_expiration,
    //     'payload'         => json_encode($pix)
    // ]);

    // 6. Retornar a view com o QR Code
    // Idealmente redirecionamos para uma rota de 'obrigado', mas para MVP isso funciona
    //return view('teste-pix', compact('pix'));
