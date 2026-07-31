@php
    $desktopBanner = 'images/organizers/' . $organizerId . '/banner.jpg';
    $mobileBanner = 'images/organizers/' . $organizerId . '/banner-mobile.jpg';
    $hasOrganizerBanner = file_exists(public_path($desktopBanner));

    // Fase 1 (v2): slides 2 e 3 usam gradiente até as imagens do Gemini serem geradas
    // (ver docs/specs/frontend-publico.md — "Geração de imagens (Gemini)").
    $slides = [
        [
            'eyebrow' => 'Corre Virtual',
            'title' => 'Desafie seus limites',
            'text' => 'Provas presenciais e virtuais, no seu ritmo, na sua rota. Escolha um evento e comece hoje.',
            'cta_label' => 'Ver Eventos',
            'cta_href' => '#eventos',
            'image' => $hasOrganizerBanner ? asset($desktopBanner) : null,
        ],
        [
            'eyebrow' => 'Todos os níveis',
            'title' => 'Do primeiro km à maratona',
            'text' => 'Modalidades e kits pra cada perfil de atleta, do iniciante ao competitivo.',
            'cta_label' => 'Inscreva-se Já',
            'cta_href' => '#eventos',
            'image' => null,
        ],
        [
            'eyebrow' => 'Comunidade',
            'title' => 'Corra em equipe',
            'text' => 'Convide amigos e família. Medalhas exclusivas te esperam na chegada.',
            'cta_label' => 'Sobre a Plataforma',
            'cta_href' => '#sobre',
            'image' => null,
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
