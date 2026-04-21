<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Subscriptions\SubscribeController;
use App\Http\Controllers\Events\EventsController;
use App\Http\Controllers\Subscriptions\PixController;

use App\Services\MercadoPagoService;

/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

Route::get('/', [EventsController::class, 'index'])->name('home');

Route::get('/event/{event_id}', [EventsController::class, 'show'])->name('event.show');

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/logout', [LoginController::class, 'logout']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Verificação Email
|--------------------------------------------------------------------------
*/

Route::get('/verify-email', [VerifyEmailController::class, 'showVerifyInputCode'])->name('verify-email.show');
Route::post('/verify-email', [VerifyEmailController::class, 'verifyEmail']);

/*
|--------------------------------------------------------------------------
| PIX
|--------------------------------------------------------------------------
*/

Route::get('/teste-pix', function () {

    $pix = MercadoPagoService::createPixPayment(
        1.00,
        'sidney.miranda2013@gmail.com'
    );

    return view('teste-pix', compact('pix'));

});

Route::post('/event-pay', [PixController::class, 'generatePix'])->name('event-pay');

/*
|--------------------------------------------------------------------------
| Inscrição
|--------------------------------------------------------------------------
*/

Route::get('/my-subscriptions', [SubscribeController::class, 'mySubscriptions'])
    ->middleware('auth')
    ->name('subscriptions.my');

Route::get('/subscribe/event/{event_id}', [SubscribeController::class, 'showSubscribeForm'])
    ->middleware('auth')
    ->name('subscribe');

Route::post('/subscribe/event/{event_id}', [SubscribeController::class, 'subscribe']);

Route::get('/subscriptions/{id}/success', [PixController::class, 'success'])->name('subscriptions.success');

