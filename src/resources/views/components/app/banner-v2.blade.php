@php
    $desktopBanner = 'images/organizers/' . $organizerId . '/banner.jpg';
    $hasOrganizerBanner = file_exists(public_path($desktopBanner));

    // Versão recortada do banner.jpg do organizador (remove o bloco de logo à esquerda,
    // mantém a ponte de Mogi Guaçu — pedido do organizador depois de ver o slide 1 com a
    // logo cortando mal). Gerada uma vez via script, não em runtime. Se não existir, cai
    // pro banner cru e depois pra imagem do Gemini.
    $organizerBannerCropped = 'images/home-v2/banner-1-organizer-cropped.jpg';
    $hasCroppedBanner = file_exists(public_path($organizerBannerCropped));

    // Imagens geradas via `php artisan images:generate-gemini` (ver docs/specs/frontend-publico.md).
    $geminiBanner1 = 'images/home-v2/banner-1.jpg';
    $geminiBanner2 = 'images/home-v2/banner-2.jpg';
    $geminiBanner3 = 'images/home-v2/banner-3.jpg';

    $slide1Image = match (true) {
        $hasCroppedBanner => asset($organizerBannerCropped),
        $hasOrganizerBanner => asset($desktopBanner),
        file_exists(public_path($geminiBanner1)) => asset($geminiBanner1),
        default => null,
    };

    $slides = [
        [
            'eyebrow' => 'Corre Virtual',
            'title' => 'Desafie seus limites',
            'text' => 'Provas presenciais e virtuais, no seu ritmo, na sua rota. Escolha um evento e comece hoje.',
            'cta_label' => 'Ver Eventos',
            'cta_href' => '#eventos',
            'image' => $slide1Image,
        ],
        [
            'eyebrow' => 'Todos os níveis',
            'title' => 'Do primeiro km à maratona',
            'text' => 'Modalidades e kits pra cada perfil de atleta, do iniciante ao competitivo.',
            'cta_label' => 'Inscreva-se Já',
            'cta_href' => '#eventos',
            'image' => file_exists(public_path($geminiBanner2)) ? asset($geminiBanner2) : null,
        ],
        [
            'eyebrow' => 'Comunidade',
            'title' => 'Corra em equipe',
            'text' => 'Convide amigos e família. Medalhas exclusivas te esperam na chegada.',
            'cta_label' => 'Sobre a Plataforma',
            'cta_href' => '#sobre',
            'image' => file_exists(public_path($geminiBanner3)) ? asset($geminiBanner3) : null,
        ],
    ];
@endphp

<section class="cv-banner" id="cv-banner" aria-roledescription="carousel">
    <div class="cv-banner__track" id="cv-banner-track">
        @foreach($slides as $index => $slide)
            <div class="cv-banner__slide {{ $index === 0 ? 'is-active' : '' }}"
                 @if($slide['image']) style="background-image: linear-gradient(180deg, rgba(13,27,42,.78), rgba(13,27,42,.9)), url('{{ $slide['image'] }}');" @endif
                 data-slide-index="{{ $index }}">
                <div class="cv-banner__content">
                    <span class="cv-banner__eyebrow">{{ $slide['eyebrow'] }}</span>
                    <h1 class="cv-banner__title">{{ $slide['title'] }}</h1>
                    <p class="cv-banner__text">{{ $slide['text'] }}</p>
                    <a href="{{ $slide['cta_href'] }}" class="cv-banner__cta">{{ $slide['cta_label'] }}</a>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" class="cv-banner__arrow cv-banner__arrow--prev" id="cv-banner-prev" aria-label="Slide anterior">&#10094;</button>
    <button type="button" class="cv-banner__arrow cv-banner__arrow--next" id="cv-banner-next" aria-label="Próximo slide">&#10095;</button>

    <div class="cv-banner__dots" id="cv-banner-dots">
        @foreach($slides as $index => $slide)
            <button type="button" class="cv-banner__dot {{ $index === 0 ? 'is-active' : '' }}" data-slide-goto="{{ $index }}" aria-label="Ir para slide {{ $index + 1 }}"></button>
        @endforeach
    </div>
</section>
