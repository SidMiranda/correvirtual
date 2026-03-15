
@extends('layouts.auth')

@section('title', 'Confirmar Email')

@section('content')

<div class="modal-overlay">
<div class="modal">

<h2>Confirme seu email</h2>

<p>Digite o código enviado para seu email</p>

<form method="POST" action="/verify-email">
@csrf

<div class="code-inputs">
<input type="number" name="d1" maxlength="1">
<input type="number" name="d2" maxlength="1">
<input type="number" name="d3" maxlength="1">
<input type="number" name="d4" maxlength="1">
</div>

<button type="submit" class="btn-primary">Confirmar</button>

</form>

</div>
</div>

@endsection

@push('scripts')
<script>

const inputs = document.querySelectorAll(".code-inputs input");

inputs.forEach((input, index) => {

input.addEventListener("input", () => {

if(input.value.length === 1 && index < inputs.length -1){
inputs[index+1].focus();
}

});

});

</script>
@endpush