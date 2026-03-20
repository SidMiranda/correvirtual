<style>
    .main-banner-container {
        position: relative;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 3rem; /* Espaçamento abaixo do componente */
    }

    .main-banner {
        width: 100%;
        overflow: hidden;
    }

    .main-banner__image {
        width: 100%;
        height: auto;
        display: block;
    }

    .events-header {
        text-align: center;
        background: linear-gradient(135deg, #1491c7 0%, #1491c7 100%);
        padding: 18px 30px;
        color: #fff;
        width: 90%;
        border: 5px solid #ffffff; /* Borda espessa e branca para melhorar o contraste */
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        margin-top: -35px; /* Efeito de sobreposição no banner */
        position: relative;
        z-index: 10;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .events-header:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
    }

    .events-header__title {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: 1px;
        display: flex;
        color: #ffffff !important; /* Garante que sobrescreve a cor global de h2 do Bootstrap */
        align-items: center;
        justify-content: center;
        gap: 12px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    .events-header__icon {
        font-size: 1.6rem;
        color: #fbbf24; /* Amarelo/Dourado para dar um destaque elegante ao ícone */
    }

    /* Responsividade para telas menores (Celulares e Tablets) */
    @media (max-width: 768px) {
        .events-header {
            width: 95%;
            padding: 15px 20px;
            margin-top: -25px;
        }
        .events-header__title {
            font-size: 1.3rem;
        }
    }
</style>

<div class="main-banner-container">
    <div class="main-banner">
        <img src="{{ asset('img/banner.jpg') }}" class="main-banner__image" alt="Banner Principal">
    </div>

    <div class="events-header">
        <h2 class="events-header__title">
            <i class="fa-solid fa-calendar-days events-header__icon"></i>
            CALENDÁRIO DE EVENTOS 2026
        </h2>
    </div>
</div>
