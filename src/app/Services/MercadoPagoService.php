<?php

namespace App\Services;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;

class MercadoPagoService
{
    public static function createPixPayment($amount, $email)
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));

        $client = new PaymentClient();

        try {

            $payment = $client->create([
                "transaction_amount" => (float) $amount,
                "description" => "Teste inscrição",
                "payment_method_id" => "pix",
                "payer" => [
                    "email" => $email
                ]
            ]);

            return $payment;

        } catch (MPApiException $e) {

            dd([
                'status' => $e->getApiResponse()->getStatusCode(),
                'content' => $e->getApiResponse()->getContent()
            ]);

        }
    }
}