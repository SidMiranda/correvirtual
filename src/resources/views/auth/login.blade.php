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

@push('scripts')
<script>
    const loginInput = document.querySelector('input[name="email_or_cpf"]');
    if(loginInput) {
        loginInput.addEventListener('input', function(e) {
            let value = e.target.value;
            // Aplica a máscara apenas se o usuário digitar padrões numéricos do CPF
            if (/^[\d.-]*$/.test(value)) {
                value = value.replace(/\D/g, "");
                if (value.length > 11) value = value.substring(0, 11);
                value = value.replace(/(\d{3})(\d)/, "$1.$2");
                value = value.replace(/(\d{3})(\d)/, "$1.$2");
                value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                e.target.value = value;
            }
        });
    }
</script>
@endpush