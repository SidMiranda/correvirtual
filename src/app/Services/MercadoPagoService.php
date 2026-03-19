<?php

namespace App\Services;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;

class MercadoPagoService
{
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

            $payment = $client->create($request);

            return $payment;

        } catch (MPApiException $e) {

            dd([
                'status' => $e->getApiResponse()->getStatusCode(),
                'content' => $e->getApiResponse()->getContent()
            ]);

        }
    }

    public static function getPayment($id)
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));

        $client = new PaymentClient();
        return $client->get($id);
    }
}
