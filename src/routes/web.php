<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Services\MercadoPagoService;
use App\Http\Controllers\RegistrationController;
use App\Models\Event;

/*
|--------------------------------------------------------------------------
| Views
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('index');
    // Busca apenas eventos ativos e ordena pela data (mais próximos primeiro)
    // $events = Event::where('active', true)
    //                ->orderBy('event_date', 'asc')
    //                ->get();

    // return view('index', compact('events'));
})->name('home');


Route::get('/evento', function () {
    return view('info-evento');
})->name('evento');

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Verificação Email
|--------------------------------------------------------------------------
*/

Route::get('/verify-email', function () {
    return view('auth.verify-email');
})->name('verify.email.form');

Route::post('/verify-email', [AuthController::class, 'verifyEmail'])
    ->name('verify.email');

/*
|--------------------------------------------------------------------------
| Teste PIX
|--------------------------------------------------------------------------
*/

Route::get('/teste-pix', function () {

    $pix = MercadoPagoService::createPixPayment(
        1.00,
        'teste@email.com'
    );

    return view('teste-pix', compact('pix'));

});

/*
|--------------------------------------------------------------------------
| Pagamento inscrição
|--------------------------------------------------------------------------
*/

Route::get('/registrations/{event_id}/register', [RegistrationController::class, 'register'])
    ->middleware('auth')
    ->name('registrations.register');