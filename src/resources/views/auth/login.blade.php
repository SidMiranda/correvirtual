@extends('layouts.auth')

@section('title','Login')

@section('content')

<div class="modal-overlay" style="display:flex">
<div class="modal">

<form method="POST" action="{{ route('login') }}" class="form-container">

@csrf

<h2>Login</h2>

<input 
type="text"
name="email_or_cpf"
placeholder="CPF ou Email"
value="{{ old('email_or_cpf') }}"
required
>

<input 
type="password"
name="password"
placeholder="Senha"
required
>

<button type="submit" class="btn-primary">
Entrar
</button>

<div class="form-links">

<a href="{{ route('register') }}">
Criar conta
</a>

</div>

</form>

</div>
</div>

@endsection