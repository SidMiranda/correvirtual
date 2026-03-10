<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Confirmar Email</title>

<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    background:#f5f5f5;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.container{
    background:white;
    padding:40px;
    border-radius:10px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    text-align:center;
}

.code-inputs{
    display:flex;
    gap:10px;
    justify-content:center;
    margin-top:20px;
}

.code-inputs input{
    width:50px;
    height:60px;
    font-size:28px;
    text-align:center;
    border:2px solid #ddd;
    border-radius:8px;
}

button{
    margin-top:20px;
    padding:10px 30px;
    border:none;
    border-radius:6px;
    background:#2e7d32;
    color:white;
    font-size:16px;
    cursor:pointer;
}

</style>

</head>
<body>

<div class="container">

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

<button type="submit">Confirmar</button>

</form>

</div>

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

</body>
</html>