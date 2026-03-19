<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use App\Http\Controllers\Controller;

class VerifyEmailController extends Controller
{
    public function showVerifyInputCode()
    {
        return view('auth.verify-email');
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
}