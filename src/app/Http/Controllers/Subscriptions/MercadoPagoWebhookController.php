<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Mail\SubscriptionConfirmed;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\MercadoPagoService;
use App\Services\MercadoPagoWebhookSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\HeaderUtils;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Registra no arquivo de Log tudo o que o Mercado Pago enviou (útil para debug)
        Log::info('Webhook Mercado Pago recebido:', $request->all());

        // 2. Valida a assinatura antes de confiar em qualquer coisa da notificação.
        // O manifest usa o data.id literal da query string. Não dá pra usar $request->query('data.id')
        // aqui: o PHP converte pontos em chave de query string para underscore ($_GET), então
        // precisamos reler a query string crua com o parser do Symfony, que preserva o ponto.
        $queryParams = HeaderUtils::parseQuery($request->getQueryString() ?? '');
        $dataIdFromQuery = $queryParams['data.id'] ?? $queryParams['id'] ?? null;

        $isValidSignature = MercadoPagoWebhookSignature::isValid(
            $request->header('x-signature'),
            $request->header('x-request-id'),
            $dataIdFromQuery,
            config('services.mercadopago.webhook_secret')
        );

        if (!$isValidSignature) {
            Log::warning('Webhook Mercado Pago rejeitado: assinatura ausente ou inválida.');
            return response()->json(['status' => 'invalid_signature'], 401);
        }

        // 3. Extrai o ID do pagamento da notificação
        // O Mercado Pago pode mandar o ID de formas diferentes dependendo do evento
        $paymentId = $request->input('data.id') ?? $request->input('id');

        if ($paymentId) {
            try {
                // 4. Consultar a API do Mercado Pago de forma segura
                $payment = MercadoPagoService::getPayment($paymentId);

                Log::info("Pagamento {$paymentId} consultado. Status: {$payment->status}");

                // 5. Se o pagamento foi aprovado, atualizamos o banco de dados
                if ($payment->status === 'approved' && isset($payment->external_reference)) {
                    $subscriptionId = $payment->external_reference;

                    // Update atômico e condicional: só marca 'paid' se ainda NÃO estava 'paid'.
                    // O Mercado Pago pode reenviar a mesma notificação (retry) — sem essa condição,
                    // duas notificações quase simultâneas passariam por uma checagem em separado
                    // (ler status, depois atualizar) e as duas mandariam o e-mail de confirmação
                    // duplicado. Com where('status', '!=', 'paid') o banco garante que só uma
                    // consegue mudar a linha; $affected diz qual.
                    $affected = Subscription::where('id', $subscriptionId)
                        ->where('status', '!=', 'paid')
                        ->update(['status' => 'paid']);

                    // Atualiza o status na tabela payments (usando o transaction_id)
                    Payment::where('transaction_id', $paymentId)->update(['status' => 'approved']);

                    Log::info("Inscrição {$subscriptionId} atualizada com sucesso para PAGO!");

                    // 5b. E-mail de confirmação só na primeira vez que a inscrição vira 'paid'.
                    // Enviado depois da resposta HTTP (dispatchAfterResponse) pra não segurar o
                    // webhook esperando o SMTP — o Mercado Pago tem timeout e reenvia se demorar.
                    // Não há worker de fila rodando neste projeto (ver docs/backlog.md), então isso
                    // roda de forma síncrona logo após a resposta ser enviada ao cliente, sem
                    // precisar de infraestrutura nova.
                    if ($affected === 1) {
                        dispatch(function () use ($subscriptionId) {
                            try {
                                $subscription = Subscription::with(['event', 'modality', 'kit', 'user'])->find($subscriptionId);

                                if ($subscription) {
                                    Mail::to($subscription->user->email)->send(new SubscriptionConfirmed($subscription));
                                }
                            } catch (\Throwable $e) {
                                Log::error("Falha ao enviar e-mail de confirmação da inscrição {$subscriptionId}: " . $e->getMessage());
                            }
                        })->afterResponse();
                    }
                }
            } catch (\Exception $e) {
                Log::error("Erro ao processar Webhook do Mercado Pago: " . $e->getMessage());
            }
        }

        // 6. Retorna Status 200 OK imediatamente para o Mercado Pago parar de enviar a mesma notificação
        return response()->json(['status' => 'success'], 200);
    }
}
