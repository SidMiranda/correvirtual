<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailCode;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // 1. Validação dos dados que vêm do seu modal
        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'sex' => 'required|in:male,female,other',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'cpf' => 'required|string|max:14|unique:users',
            'password' => 'required|string|min:6', // No futuro colocamos regras mais fortes
        ]);

        // 2. Criação do usuário no banco
        $user = User::create([
            'name' => $request->name,
            'birth_date' => $request->birth_date,
            'sex' => $request->sex,
            'phone' => $request->phone,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'password' => Hash::make($request->password),
            'role' => 'athlete', // Já força o papel correto
            'active' => true,
        ]);

        $code = random_int(1000, 9999);

        $user->email_verification_code = $code;
        $user->save();

        Mail::to($user->email)->send(new VerifyEmailCode($code));

        session(['verification_email' => $user->email]);

        return redirect()->route('verify.email')
            ->with('success', 'Enviamos um código de verificação para seu email.');
    }

    public function verifyEmail(Request $request)
    {
        if (!session('verification_email')) {
            return redirect()->route('home');
        }

        $code = $request->d1.$request->d2.$request->d3.$request->d4;

        $user = User::where('email', session('verification_email'))
            ->where('email_verification_code', $code)
            ->first();

        if (!$user) {
            return back()->with('error', 'Código inválido.');
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->save();

        session()->forget('verification_email');

        Auth::login($user);

        return redirect()->route('home')
            ->with('success', 'Email confirmado com sucesso!');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email_or_cpf' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->email_or_cpf;

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'cpf';

        if (Auth::attempt([$field => $login, 'password' => $request->password])) {

            $request->session()->regenerate();

            $user = Auth::user();

            // 🔒 Verifica se o email foi confirmado
            if (!$user->email_verified_at) {

                Auth::logout();

                return back()->withErrors([
                    'email_or_cpf' => 'Você precisa confirmar seu email antes de entrar.'
                ]);
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