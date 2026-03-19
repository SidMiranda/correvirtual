<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Registra no arquivo de Log tudo o que o Mercado Pago enviou (útil para debug)
        Log::info('Webhook Mercado Pago recebido:', $request->all());

        // 2. Extrai o ID do pagamento da notificação
        // O Mercado Pago pode mandar o ID de formas diferentes dependendo do evento
        $paymentId = $request->input('data.id') ?? $request->input('id');

        if ($paymentId) {
            try {
                // 3. Consultar a API do Mercado Pago de forma segura
                $payment = MercadoPagoService::getPayment($paymentId);

                Log::info("Pagamento {$paymentId} consultado. Status: {$payment->status}");

                // 4. Se o pagamento foi aprovado, atualizamos o banco de dados
                if ($payment->status === 'approved' && isset($payment->external_reference)) {
                    $subscriptionId = $payment->external_reference;

                    // Atualiza a Inscrição para 'paid'
                    Subscription::where('id', $subscriptionId)->update(['status' => 'paid']);

                    // Atualiza o status na tabela payments (usando o transaction_id)
                    Payment::where('transaction_id', $paymentId)->update(['status' => 'approved']);

                    Log::info("Inscrição {$subscriptionId} atualizada com sucesso para PAGO!");
                }
            } catch (\Exception $e) {
                Log::error("Erro ao processar Webhook do Mercado Pago: " . $e->getMessage());
            }
        }

        // 3. Retorna Status 200 OK imediatamente para o Mercado Pago parar de enviar a mesma notificação
        return response()->json(['status' => 'success'], 200);
    }
}
