<?php

namespace App\Support;

use App\Models\Event;

/**
 * O único lugar que sabe montar URL de imagem pública.
 *
 * Antes disso, três views (`event-card`, `my-subscriptions`, `main-banner`)
 * montavam o caminho na mão e decidiam o fallback com
 * `file_exists(public_path(...))`. Isso tinha dois problemas:
 *
 * 1. A regra estava copiada em três lugares, com pequenas diferenças.
 * 2. `file_exists` olha o disco do container. Com a imagem no R2, ele responde
 *    `false` sempre — e o site não quebra, só passa a mostrar o fallback em
 *    TODA imagem, em silêncio. Falha silenciosa é pior que erro.
 *
 * Aqui a existência da imagem é decidida pelo BANCO (`events.banner_url`
 * preenchido), não pelo disco, e o `onerror` do <img> cobre o caso de o
 * arquivo estar faltando de verdade. Funciona igual no disco local e no CDN.
 *
 * Ver docs/specs/armazenamento-r2.md.
 */
class Arquivos
{
    /**
     * Prefixo das URLs públicas. Sem CDN configurado, aponta para
     * `public/images` do próprio container — que tem exatamente a mesma
     * estrutura de pastas do bucket.
     */
    public static function base(): string
    {
        $cdn = config('arquivos.base_url');

        return $cdn !== '' ? $cdn : rtrim(asset('images'), '/');
    }

    public static function url(string $caminhoRelativo): string
    {
        return self::base() . '/' . ltrim($caminhoRelativo, '/');
    }

    /*
    |--------------------------------------------------------------------------
    | Imagens de evento
    |--------------------------------------------------------------------------
    */

    /** Imagem retangular usada nos cards de listagem. */
    public static function cardDoEvento(Event $event): string
    {
        return $event->banner_url
            ? self::url("organizadores/{$event->organizer_id}/eventos/{$event->id}/card.jpg")
            : self::cardPadrao();
    }

    /** Imagem grande, no topo da página do evento. */
    public static function bannerDoEvento(Event $event): string
    {
        return $event->banner_url
            ? self::url("organizadores/{$event->organizer_id}/eventos/{$event->id}/banner.jpg")
            : self::bannerPadrao();
    }

    /*
    |--------------------------------------------------------------------------
    | Imagens do organizador
    |--------------------------------------------------------------------------
    */

    public static function logoDoOrganizador(int $organizerId): string
    {
        return self::url("organizadores/{$organizerId}/logo.png");
    }

    public static function bannerDoOrganizador(int $organizerId): string
    {
        return self::url("organizadores/{$organizerId}/banner.jpg");
    }

    public static function bannerMobileDoOrganizador(int $organizerId): string
    {
        return self::url("organizadores/{$organizerId}/banner-mobile.jpg");
    }

    public static function sobreNosDoOrganizador(int $organizerId): string
    {
        return self::url("organizadores/{$organizerId}/sobre-nos.jpg");
    }

    /**
     * O organizador tem imagem de marca própria?
     *
     * Diferente do evento, o organizador não tem no banco nenhum campo dizendo
     * quais imagens ele possui — o código sempre olhou o disco. Enquanto for
     * disco, dá pra continuar olhando; com CDN, não dá (seria uma requisição
     * de rede por página), então assume-se que existe e o `onerror` do <img>
     * cobre a falta.
     *
     * Consequência a assumir de olhos abertos: com CDN ligado, um organizador
     * sem banner não mostra mais a faixa com nome/e-mail que aparecia no lugar.
     * A correção de verdade é dar ao organizador campos de imagem como o evento
     * tem — está registrado em docs/specs/armazenamento-r2.md.
     */
    public static function organizadorTem(string $caminhoRelativo): bool
    {
        if (config('arquivos.base_url') !== '') {
            return true;
        }

        return file_exists(public_path('images/' . ltrim($caminhoRelativo, '/')));
    }

    /*
    |--------------------------------------------------------------------------
    | Imagens da plataforma (não pertencem a organizador nenhum)
    |--------------------------------------------------------------------------
    */

    public static function imagemDaHome(string $arquivo): string
    {
        return self::url("plataforma/home/{$arquivo}");
    }

    public static function cardPadrao(): string
    {
        return self::url('plataforma/padrao/card.jpg');
    }

    public static function bannerPadrao(): string
    {
        return self::url('plataforma/padrao/banner.jpg');
    }

    public static function usuarioPadrao(): string
    {
        return self::url('plataforma/padrao/user.jpg');
    }
}
