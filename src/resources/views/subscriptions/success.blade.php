<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Aprovado!</title>
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    <style>
        .success-icon {
            font-size: 80px;
            color: #1db954; /* Cor de sucesso do seu forms.css */
            margin-bottom: 10px;
            animation: scaleIn 0.5s ease-in-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal">
            <div class="success-icon">✓</div>
            <h2>Pagamento Aprovado!</h2>
            <p>Sua inscrição foi confirmada com sucesso. Prepare-se para a corrida!</p>
            <a href="/my-subscriptions" class="btn-primary" style="display: block; text-decoration: none; box-sizing: border-box; margin-top: 20px;">Ver minhas inscrições</a>
        </div>
    </div>
</body>
</html>
