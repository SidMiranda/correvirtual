<?php

namespace App\Services;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    /**
     * @return object|null O pagamento criado, ou null se a API do Mercado Pago recusar/falhar
     *                      (credencial inválida, instabilidade, etc.) — quem chama decide o
     *                      que mostrar ao usuário.
     */
    public static function createPixPayment($amount, $email, $externalReference = null)
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));

        $client = new PaymentClient();

        try {
            $request = [
                "transaction_amount" => (float) $amount,
                "description" => "Teste inscrição",
                "payment_method_id" => "pix",
                "payer" => [
                    "email" => $email
                ]
            ];

            if ($externalReference) {
                $request["external_reference"] = (string) $externalReference;
            }

            return $client->create($request);

        } catch (MPApiException $e) {
            Log::error('Falha ao criar pagamento Pix no Mercado Pago', [
                'status' => $e->getApiResponse()?->getStatusCode(),
                'content' => $e->getApiResponse()?->getContent(),
                'external_reference' => $externalReference,
            ]);

            return null;
        }
    }

    public static function getPayment($id)
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));

        $client = new PaymentClient();
        return $client->get($id);
    }
}
