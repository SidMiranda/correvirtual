<style>
    .main-banner-container {
        position: relative;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .main-banner {
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .main-banner__image {
        width: 100%;
        height: auto;
        display: block;
    }

    .default-banner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 20px;
    }

    .organizer-banner-title {
        font-size: 3rem;
        font-weight: 800;
        color: #ffffff;
        text-transform: uppercase;
        text-shadow: 0 0 15px rgba(0, 0, 0, 0.8), 0 0 30px rgba(0, 0, 0, 0.6);
        margin-bottom: 10px;
    }

    .organizer-banner-email {
        font-size: 1.2rem;
        color: #e0e0e0;
        font-weight: 500;
        text-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
    }

    @media (max-width: 768px) {
        .organizer-banner-title {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        .organizer-banner-email {
            font-size: 1rem;
        }
    }
</style>

<div class="main-banner-container">
    <div class="main-banner">
        @php
            use App\Support\Arquivos;

            $temDesktop = Arquivos::organizadorTem("organizadores/{$organizerId}/banner.jpg");
            $temMobile  = Arquivos::organizadorTem("organizadores/{$organizerId}/banner-mobile.jpg");
        @endphp

        @if($temDesktop || $temMobile)
            <picture>
                @if($temMobile)
                    <source media="(max-width: 1024px)" srcset="{{ Arquivos::bannerMobileDoOrganizador($organizerId) }}">
                @endif
                <img src="{{ $temDesktop ? Arquivos::bannerDoOrganizador($organizerId) : Arquivos::bannerMobileDoOrganizador($organizerId) }}"
                     onerror="this.onerror=null;this.src='{{ Arquivos::bannerPadrao() }}';"
                     class="main-banner__image" alt="Banner Principal">
            </picture>
        @else
            {{-- Não existe banner-mobile padrão (nunca existiu — o código antigo
                 referenciava images/default/banner-mobile.jpg, que não está no
                 repositório). O banner padrão único serve os dois tamanhos. --}}
            <picture>
                <img src="{{ Arquivos::bannerPadrao() }}" class="main-banner__image" alt="Banner Principal">
            </picture>
            <div class="default-banner-overlay">
                <h1 class="organizer-banner-title">{{ $organizerName }}</h1>
                <p class="organizer-banner-email">{{ $organizerEmail }}</p>
            </div>
        @endif
    </div>
</div>
