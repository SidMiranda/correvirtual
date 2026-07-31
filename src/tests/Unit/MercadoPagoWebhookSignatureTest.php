<?php

namespace Tests\Unit;

use App\Services\MercadoPagoWebhookSignature;
use PHPUnit\Framework\TestCase;

class MercadoPagoWebhookSignatureTest extends TestCase
{
    private function signatureHeaderFor(string $dataId, string $requestId, string $ts, string $secret): string
    {
        $manifest = 'id:' . strtolower($dataId) . ";request-id:{$requestId};ts:{$ts};";
        $v1 = hash_hmac('sha256', $manifest, $secret);

        return "ts={$ts},v1={$v1}";
    }

    public function test_accepts_a_correctly_signed_notification(): void
    {
        $header = $this->signatureHeaderFor('123456789', 'req-1', '1700000000', 'segredo');

        $this->assertTrue(
            MercadoPagoWebhookSignature::isValid($header, 'req-1', '123456789', 'segredo')
        );
    }

    public function test_rejects_when_v1_does_not_match(): void
    {
        $header = $this->signatureHeaderFor('123456789', 'req-1', '1700000000', 'segredo-diferente');

        $this->assertFalse(
            MercadoPagoWebhookSignature::isValid($header, 'req-1', '123456789', 'segredo')
        );
    }

    public function test_rejects_when_data_id_was_tampered(): void
    {
        $header = $this->signatureHeaderFor('123456789', 'req-1', '1700000000', 'segredo');

        $this->assertFalse(
            MercadoPagoWebhookSignature::isValid($header, 'req-1', '999999999', 'segredo')
        );
    }

    public function test_rejects_missing_signature_header(): void
    {
        $this->assertFalse(
            MercadoPagoWebhookSignature::isValid(null, 'req-1', '123456789', 'segredo')
        );
    }

    public function test_rejects_missing_request_id(): void
    {
        $header = $this->signatureHeaderFor('123456789', 'req-1', '1700000000', 'segredo');

        $this->assertFalse(
            MercadoPagoWebhookSignature::isValid($header, null, '123456789', 'segredo')
        );
    }

    public function test_rejects_when_secret_is_not_configured(): void
    {
        $header = $this->signatureHeaderFor('123456789', 'req-1', '1700000000', 'segredo');

        $this->assertFalse(
            MercadoPagoWebhookSignature::isValid($header, 'req-1', '123456789', null)
        );
    }

    public function test_rejects_malformed_signature_header(): void
    {
        $this->assertFalse(
            MercadoPagoWebhookSignature::isValid('not-a-valid-header', 'req-1', '123456789', 'segredo')
        );
    }
}
