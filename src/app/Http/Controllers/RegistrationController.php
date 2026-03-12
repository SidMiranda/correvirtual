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
    public function store(Request $request, $eventId)
    {
        // 1. Validação: Garante que estamos recebendo IDs válidos do formulário
        $request->validate([
            'modality_id' => 'required|exists:event_modalities,id',
            'kit_id'      => 'required|exists:event_kits,id',
        ]);

        // 2. Recuperar os objetos do banco para pegar preço e nomes corretos
        // Nota: Nunca confie no preço vindo do front-end (HTML), busque sempre do banco.
        $modality = EventModality::findOrFail($request->modality_id);
        $kit      = EventKit::findOrFail($request->kit_id);

        // 3. Criar a Inscrição (Intenção de compra)
        // O status nasce como 'pending'
        $registration = Registration::create([
            'event_id' => $eventId,
            'user_id'  => auth()->id(),
            'distance' => $modality->name, // A migration pede string (ex: '5km')
            'kit'      => $kit->name,      // A migration pede string (ex: 'Kit Básico')
            'price'    => $kit->price,     // Preço vem do Kit
            'status'   => 'pending',
        ]);

        // 4. Gerar o PIX no Mercado Pago
        // Agora usamos o valor real ($registration->price) e não mais 10.00 fixo
        $pix = MercadoPagoService::createPixPayment(
            (float) $registration->price,
            auth()->user()->email
        );

        // 5. Salvar o registro do Pagamento
        Payment::create([
            'registration_id' => $registration->id,
            'transaction_id'  => $pix->id,
            'qr_code'         => $pix->point_of_interaction->transaction_data->qr_code,
            'qr_code_base64'  => $pix->point_of_interaction->transaction_data->qr_code_base64,
            'ticket_url'      => $pix->point_of_interaction->transaction_data->ticket_url,
            'expires_at'      => $pix->date_of_expiration,
            'payload'         => json_encode($pix)
        ]);

        // 6. Retornar a view com o QR Code
        // Idealmente redirecionamos para uma rota de 'obrigado', mas para MVP isso funciona
        return view('teste-pix', compact('pix'));
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
}
