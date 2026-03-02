<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rotas das Views
Route::get('/', function () {
    return view('index');
})->name('home');

Route::get('/evento', function () {
    return view('info-evento');
})->name('evento');

// Rotas de Autenticação
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
