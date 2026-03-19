<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailCode;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        //verifica se o usuário já esta logado

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email_or_cpf' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->email_or_cpf;

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'cpf';

        // Se for CPF (não passou na validação de e-mail), removemos a pontuação antes de buscar no banco
        if ($field === 'cpf') {
            $login = preg_replace('/[^0-9]/', '', $login);
        }

        if (Auth::attempt([$field => $login, 'password' => $request->password])) {

            $request->session()->regenerate();

            $user = Auth::user();

            // 🔒 Verifica se o email foi confirmado
            if (!$user->email_verified_at) {

                // Gera um novo código, salva e reenvia o email
                $code = random_int(1000, 9999);
                $user->email_verification_code = $code;
                $user->save();

                Mail::to($user->email)->send(new VerifyEmailCode($code));

                // Salva o email na sessão para o VerifyEmailController conseguir validar
                session(['verification_email' => $user->email]);

                Auth::logout();

                return redirect()->route('verify-email.show')
                    ->with('error', 'Você precisa confirmar seu email antes de entrar. Um novo código foi enviado para o seu email.');
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email_or_cpf' => 'Credenciais inválidas.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }


}