<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Pagamento PIX</title>
<link rel="stylesheet" href="{{ asset('css/teste-pix.css') }}">

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