@props([
    'titulo' => null,
    'descricao' => null,
    'imagem' => null,
    'tipo' => 'website',
])

{{--
    Cartão de pré-visualização do link (Open Graph + Twitter Card) — é o que o
    WhatsApp, o Instagram e o Facebook mostram quando alguém cola o endereço.

    Sem isso o link chega como texto cru, sem imagem nem descrição, o que passa
    exatamente a impressão errada de uma plataforma que cobra dinheiro.

    A imagem precisa de URL absoluta: quem monta a prévia é um servidor de fora,
    que não sabe resolver caminho relativo. As URLs do CDN já são absolutas; o
    fallback usa `url()` para garantir isso também quando o CDN está desligado.
--}}

@php
    use App\Support\Arquivos;

    $organizador = $organizerName ?? config('app.name');

    $ogTitulo = $titulo ?: $organizador;

    $ogDescricao = \Illuminate\Support\Str::limit(
        trim(preg_replace('/\s+/', ' ', strip_tags($descricao ?: 'Inscrições para corridas de rua e desafios virtuais. Escolha seu evento, sua distância e garanta seu kit.'))),
        180
    );

    // Sem imagem específica, cai no banner do organizador; sem ele, no banner
    // padrão da plataforma. Um cartão sem imagem é bem pior que um genérico.
    $ogImagem = $imagem
        ?: (isset($organizerId) && $organizerId
            ? Arquivos::bannerDoOrganizador($organizerId)
            : Arquivos::bannerPadrao());

    if (!\Illuminate\Support\Str::startsWith($ogImagem, ['http://', 'https://'])) {
        $ogImagem = url($ogImagem);
    }
@endphp

<meta name="description" content="{{ $ogDescricao }}">

<meta property="og:type" content="{{ $tipo }}">
<meta property="og:site_name" content="{{ $organizador }}">
<meta property="og:locale" content="pt_BR">
<meta property="og:title" content="{{ $ogTitulo }}">
<meta property="og:description" content="{{ $ogDescricao }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $ogImagem }}">
<meta property="og:image:alt" content="{{ $ogTitulo }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitulo }}">
<meta name="twitter:description" content="{{ $ogDescricao }}">
<meta name="twitter:image" content="{{ $ogImagem }}">
