
@extends('layouts.auth')

@section('title', 'Confirmar Email')

@section('content')

<div class="modal-overlay">
<div class="modal">

<h2>Confirme seu email</h2>

<p>Digite o código enviado para seu email</p>

<form method="POST" action="/verify-email" id="verify-form">
@csrf

<div class="code-inputs">
<input type="text" name="d1" maxlength="1" inputmode="numeric" pattern="[0-9]*">
<input type="text" name="d2" maxlength="1" inputmode="numeric" pattern="[0-9]*">
<input type="text" name="d3" maxlength="1" inputmode="numeric" pattern="[0-9]*">
<input type="text" name="d4" maxlength="1" inputmode="numeric" pattern="[0-9]*">
</div>

<button type="submit" class="btn-primary" style="background-color: #0d6efd; border-color: #0d6efd; color: #fff;">Confirmar</button>

</form>

</div>
</div>

@endsection

@push('scripts')
<script>

const inputs = document.querySelectorAll(".code-inputs input");
const form = document.getElementById("verify-form");

inputs.forEach((input, index) => {

input.addEventListener("input", (e) => {
    // Remove qualquer caractere digitado que não seja número
    e.target.value = e.target.value.replace(/\D/g, "");

    if(e.target.value.length === 1) {
        if(index < inputs.length - 1){
            inputs[index+1].focus();
        } else {
            // No último dígito, submete o formulário automaticamente
            form.submit();
        }
    }
});

// Permite voltar de input apagando com a tecla Backspace
input.addEventListener("keydown", (e) => {
    if(e.key === "Backspace" && e.target.value === "" && index > 0) {
        inputs[index-1].focus();
    }
});

});

</script>
@endpush