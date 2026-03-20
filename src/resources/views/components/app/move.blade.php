<style>
    /* Reset básico para o componente não herdar margens indesejadas */
    .cv-container * {
        box-sizing: border-box;
    }

    /* Container Principal */
    .cv-container {
        display: flex;
        gap: 24px;
        max-width: 1200px;
        margin: 20px auto;
        font-family: 'Montserrat', 'Segoe UI', Roboto, Helvetica, sans-serif; /* Fontes modernas */
        align-items: stretch; /* Faz as duas divs terem a mesma altura */
    }

    /* Card da Esquerda (Texto) */
    .cv-text-card {
        flex: 1;
        background-color: #ffffff;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
    }

    /* Tag "Sobre o Evento" */
    .cv-badge {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #1a1a1a;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cv-dot {
        width: 12px;
        height: 12px;
        background-color: #cddc39; /* Verde limão/Amarelo */
        border-radius: 50%;
        display: inline-block;
    }

    /* Título */
    .cv-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0d1b2a; /* Azul bem escuro */
        margin: 0 0 20px 0;
        line-height: 1.3;
    }

    /* Corpo do Texto */
    .cv-description {
        color: #666666;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 30px;
        flex-grow: 1; /* Empurra o botão para baixo se sobrar espaço */
    }

    .cv-description p {
        margin: 0 0 16px 0;
    }

    /* Botão */
    .cv-btn {
        background-color: #1a71b2; /* Verde limão/Amarelo do print */
        color: #fff;
        font-weight: 800;
        font-size: 14px;
        text-transform: uppercase;
        text-decoration: none;
        padding: 14px 28px;
        border-radius: 30px;
        display: inline-block;
        text-align: center;
        transition: background-color 0.3s ease, transform 0.2s ease;
        align-self: flex-start; /* Evita que o botão estique na largura inteira */
    }

    .cv-btn:hover {
        background-color: #1a71b2;
        transform: translateY(-2px);
    }

    /* Card da Direita (Vídeo) */
    .cv-video-card {
        flex: 1;
        background-color: #2C2C2C;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: flex;
    }

    /* Wrapper da Imagem/Vídeo para ocupar todo o card */
    .cv-video-wrapper {
        width: 100%;
        height: 100%;
        position: relative;
    }

    .cv-video-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Responsividade para Mobile */
    @media (max-width: 768px) {
        .cv-container {
            flex-direction: column; /* Coloca o texto em cima e vídeo embaixo */
            padding: 0 15px;
        }

        .cv-text-card {
            padding: 30px 20px;
        }

        .cv-title {
            font-size: 22px;
        }

        .cv-btn {
            width: 100%; /* Botão ocupa 100% no celular para facilitar o clique */
        }
    }
</style>

<div class="cv-container">

    <div class="cv-text-card">
        <div class="cv-badge">
            <span class="cv-dot"></span> SOBRE A PLATAFORMA
        </div>

        <h2 class="cv-title">Corre Virtual - Desafie seus limites</h2>

        <div class="cv-description">
            <p>Uma experiência completa de treinos e corridas: a mesma energia de prova, com a flexibilidade de correr no seu tempo, na sua rota favorita e no seu ritmo.</p>

            <p>Ideal para atletas de todos os níveis. Não importa se você está dando seus primeiros passos na corrida ou se já busca quebrar seus recordes pessoais, temos o desafio perfeito para você.</p>

            <p>Venha com amigos, família e seu time de treinos. A <strong>Corre Virtual</strong> é a comunidade que combina saúde, diversão e o sentimento único de conquista, enviando medalhas exclusivas direto para a sua casa.</p>

            <p>Transforme cada quilômetro em uma vitória. <strong>Vamos juntos!</strong></p>
        </div>

        <a href="#" class="cv-btn">COMEÇAR MEU DESAFIO</a>
    </div>

    <div class="cv-video-card">
        <div class="cv-video-wrapper">
            <img src="{{ asset('images/sobre-nos.jpg') }}" alt="Vídeo de Apresentação" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    </div>

</div>


<script async src="//www.instagram.com/embed.js"></script>
