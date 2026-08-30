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

    // Sem imagem específica, cai no cartão do organizador; sem ele, no da
    // plataforma. Um cartão sem imagem é bem pior que um genérico.
    //
    // Todas essas imagens são 1200x630 — é a proporção que o WhatsApp e o
    // Facebook esperam, e é o que autoriza declarar as dimensões abaixo. Elas
    // são geradas pelo comando `og:gerar` (ver App\Support\ImagemOg); nunca
    // aponte esta meta para uma imagem de outro formato sem trocar as
    // dimensões junto.
    $ogImagem = $imagem
        ?: (isset($organizerId) && $organizerId
            ? Arquivos::ogDoOrganizador($organizerId)
            : Arquivos::ogPadrao());

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
<meta property="og:image:secure_url" content="{{ $ogImagem }}">
{{-- Dimensões declaradas: com elas o WhatsApp e o Facebook montam o cartão
     grande já na primeira leitura, sem esperar o download da imagem para
     descobrir o formato. Toda imagem que sai daqui é 1200x630 (ver
     App\Support\ImagemOg). --}}
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $ogTitulo }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitulo }}">
<meta name="twitter:description" content="{{ $ogDescricao }}">
<meta name="twitter:image" content="{{ $ogImagem }}">
