<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscriptions\MercadoPagoWebhookController;
use App\Http\Controllers\Subscriptions\PixController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle']);

// Rota para a tela do PIX ficar consultando o status a cada 3 segundos
Route::get('/subscriptions/{id}/status', [PixController::class, 'checkStatus']);
