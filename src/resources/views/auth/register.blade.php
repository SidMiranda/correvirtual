@extends('layouts.auth')

@section('title','Registrar')

@section('content')

<div class="modal-overlay" style="display:flex">
<div class="modal">

<form method="POST" action="{{ route('register') }}" class="form-container">

@csrf

<h2>Registrar</h2>

<input 
type="text"
name="name"
placeholder="Nome completo"
value="{{ old('name') }}"
required
>

<input 
type="date"
name="birth_date"
value="{{ old('birth_date') }}"
required
>

<select name="sex" required>

<option value="">Sexo</option>

<option value="male">
Masculino
</option>

<option value="female">
Feminino
</option>

<option value="other">
Outro
</option>

</select>

<input 
type="text"
name="phone"
placeholder="Celular"
value="{{ old('phone') }}"
required
>

<input 
type="email"
name="email"
placeholder="Email"
value="{{ old('email') }}"
required
>

<input 
type="text"
name="cpf"
placeholder="CPF"
value="{{ old('cpf') }}"
required
>

<input 
type="password"
name="password"
placeholder="Senha"
required
>

<button type="submit" class="btn-primary">
Registrar
</button>

<div class="form-links">

<a href="{{ route('login') }}">
Já tenho conta
</a>

</div>

</form>

</div>
</div>

@endsection

@push('scripts')
<script>
    const cpfInput = document.querySelector('input[name="cpf"]');
    if(cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ""); // Remove tudo que não for número
            if (value.length > 11) value = value.substring(0, 11); // Limita a 11 dígitos
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            e.target.value = value;
        });
    }

    const phoneInput = document.querySelector('input[name="phone"]');
    if(phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ""); // Remove tudo que não for número
            if (value.length > 11) value = value.substring(0, 11); // Limita a 11 dígitos (DDD + 9 dígitos)
            value = value.replace(/^(\d{2})(\d)/g, "($1) $2"); // Coloca parênteses no DDD
            value = value.replace(/(\d)(\d{4})$/, "$1-$2"); // Coloca o hífen antes dos últimos 4 dígitos
            e.target.value = value;
        });
    }
</script>
@endpush