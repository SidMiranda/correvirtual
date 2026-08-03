<?php

namespace App\Services;

use App\Models\Subscription;

class PixAmountResolver
{
    /**
     * TEMPORÁRIO — teste, reverter para (float) $subscription->price quando o Sidney
     * liberar (BUG-001, ver docs/backlog.md). Enquanto MERCADOPAGO_TEST_PRICE_ENABLED=true,
     * toda cobrança Pix sai no valor de MERCADOPAGO_TEST_PRICE_VALUE, não no preço real
     * do kit — não mexe em Subscription::price, só no valor cobrado de fato.
     */
    public static function resolve(Subscription $subscription): float
    {
        return config('services.mercadopago.test_price_enabled')
            ? (float) config('services.mercadopago.test_price_value')
            : (float) $subscription->price;
    }
}
