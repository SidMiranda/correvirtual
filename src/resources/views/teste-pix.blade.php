<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Pagamento PIX</title>

<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    background:#f4f6f9;
    margin:0;
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}

.container{
    background:white;
    padding:40px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    max-width:420px;
    width:90%;
    text-align:center;
}

h1{
    margin-top:0;
    font-size:24px;
    color:#222;
}

.subtitle{
    color:#666;
    margin-bottom:25px;
}

.qrcode{
    margin:20px 0;
}

.qrcode img{
    width:230px;
    height:auto;
}

.pix-code{
    width:100%;
    margin-top:10px;
    padding:10px;
    border-radius:6px;
    border:1px solid #ddd;
    font-size:13px;
    resize:none;
}

.copy-btn{
    margin-top:10px;
    background:#009ee3;
    color:white;
    border:none;
    padding:10px 18px;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

.copy-btn:hover{
    background:#0084bf;
}

.status{
    margin-top:15px;
    font-size:13px;
    color:#888;
}

.ticket-link{
    margin-top:20px;
    display:block;
    color:#009ee3;
    text-decoration:none;
    font-size:14px;
}

@media(max-width:500px){

    .container{
        padding:25px;
    }

    .qrcode img{
        width:200px;
    }

}

</style>

</head>

<body>

<div class="container">

<h1>Pague com PIX</h1>

<p class="subtitle">
Escaneie o QR Code ou copie o código abaixo para pagar sua inscrição
</p>

<div class="qrcode">
<img src="data:image/png;base64,{{ $pix->point_of_interaction->transaction_data->qr_code_base64 }}">
</div>

<textarea id="pixCode" class="pix-code" rows="4">
{{ $pix->point_of_interaction->transaction_data->qr_code }}
</textarea>

<button class="copy-btn" onclick="copiarPix()">
Copiar código PIX
</button>

<p class="status">
Pagamento aguardando confirmação...
</p>

<a class="ticket-link" href="{{ $pix->point_of_interaction->transaction_data->ticket_url }}" target="_blank">
Abrir página do pagamento
</a>

</div>

<script>

function copiarPix(){

    const campo = document.getElementById("pixCode");

    campo.select();
    campo.setSelectionRange(0,99999);

    navigator.clipboard.writeText(campo.value);

    alert("Código PIX copiado!");

}

</script>

</body>
</html>