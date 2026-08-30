<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Team;

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
            ? self::comVersao(self::url("organizadores/{$event->organizer_id}/eventos/{$event->id}/card.jpg"), $event)
            : self::cardPadrao();
    }

    /** Imagem grande, no topo da página do evento. */
    public static function bannerDoEvento(Event $event): string
    {
        return $event->banner_url
            ? self::comVersao(self::url("organizadores/{$event->organizer_id}/eventos/{$event->id}/banner.jpg"), $event)
            : self::bannerPadrao();
    }

    /**
     * Acrescenta ?v={updated_at} à URL da imagem.
     *
     * O caminho no bucket é derivado do id e não muda quando a imagem é
     * trocada — e ela sobe com `Cache-Control: immutable`, então o CDN guarda a
     * versão antiga por um ano. Sem este parâmetro, trocar a arte de um evento
     * não aparece para ninguém.
     *
     * Foi assim que a primeira carga de eventos saiu com as artes trocadas: o
     * ambiente de desenvolvimento havia escrito nos mesmos caminhos antes, e o
     * CDN continuou servindo aquelas imagens.
     */
    private static function comVersao(string $url, Event $event): string
    {
        $versao = $event->updated_at?->timestamp;

        return $versao ? "{$url}?v={$versao}" : $url;
    }

    /*
    |--------------------------------------------------------------------------
    | Imagens do organizador
    |--------------------------------------------------------------------------
    */

    /**
     * Aceita nulo: em admin.correvirtual.com.br não existe organizador no
     * domínio, e a tela de login é a mesma view pública. Sem esse caso, o
     * favicon montaria `organizadores//logo.png`.
     */
    public static function logoDoOrganizador(?int $organizerId): ?string
    {
        return $organizerId ? self::url("organizadores/{$organizerId}/logo.png") : null;
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
    | Brasão da equipe
    |--------------------------------------------------------------------------
    */

    /**
     * Devolve nulo quando a equipe não tem brasão, para a tela decidir o que
     * mostrar no lugar (as iniciais) em vez de exibir imagem quebrada.
     */
    public static function brasaoDaEquipe(Team $team): ?string
    {
        return $team->has_logo
            ? self::url("organizadores/{$team->organizer_id}/equipes/{$team->id}/brasao.jpg")
            : null;
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
