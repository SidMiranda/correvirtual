<?php

namespace App\Services;

class MercadoPagoWebhookSignature
{
    /**
     * Valida o header x-signature enviado pelo Mercado Pago.
     * Algoritmo documentado pelo MP: HMAC-SHA256 do manifest
     * "id:{data.id};request-id:{x-request-id};ts:{ts};" usando o
     * webhook secret configurado no painel, comparado ao "v1" do header.
     */
    public static function isValid(?string $signatureHeader, ?string $requestId, ?string $dataId, ?string $secret): bool
    {
        if (!$signatureHeader || !$requestId || !$dataId || !$secret) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $pair) {
            [$key, $value] = array_pad(explode('=', trim($pair), 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key] = $value;
            }
        }

        $ts = $parts['ts'] ?? null;
        $v1 = $parts['v1'] ?? null;

        if (!$ts || !$v1) {
            return false;
        }

        $manifest = 'id:' . strtolower($dataId) . ";request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $v1);
    }
}
