<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validação dos dados que vêm do seu modal
        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'cpf' => 'required|string|max:14|unique:users',
            'password' => 'required|string|min:6', // No futuro colocamos regras mais fortes
        ]);

        // 2. Criação do usuário no banco
        $user = User::create([
            'name' => $request->name,
            'birth_date' => $request->birth_date,
            'phone' => $request->phone,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'password' => Hash::make($request->password),
            'role' => 'athlete', // Já força o papel correto
            'active' => true,
        ]);

        // 3. Loga o usuário automaticamente após o cadastro
        Auth::login($user);

        // 4. Redireciona de volta (ou para um painel)
        return redirect()->back()->with('success', 'Cadastro realizado com sucesso!');
    }
}