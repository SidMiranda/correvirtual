<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organizer;

/**
 * Base dos controllers do painel.
 *
 * O escopo do painel é o organizador do USUÁRIO LOGADO — não o do domínio da
 * requisição, como no site público (ver docs/specs/painel-admin.md). Todo
 * controller do painel parte daqui para não repetir esse detalhe (e não errar).
 */
abstract class AdminController extends Controller
{
    /**
     * ID do organizador do usuário logado. O middleware `organizer.admin` já
     * garantiu que existe, então aqui nunca é nulo.
     */
    protected function organizerId(): int
    {
        return (int) auth()->user()->organizer_id;
    }

    protected function organizer(): Organizer
    {
        return auth()->user()->organizer;
    }

    /**
     * O evento, já filtrado pelo organizador do usuário logado.
     *
     * 404 e não 403 de propósito: um organizador não deve nem descobrir que o
     * evento de outro existe. Nunca buscar por ID solto e conferir depois — é
     * assim que nasce o vazamento entre clientes (BUG-005).
     */
    protected function eventoDoOrganizador(int $id): Event
    {
        return Event::where('id', $id)
            ->where('organizer_id', $this->organizerId())
            ->firstOrFail();
    }

    /**
     * O evento, exigindo que ele ainda não tenha acontecido.
     *
     * Prova já realizada não tem mais modalidade nem kit para mexer: alterar
     * depois da corrida bagunça o histórico de quem se inscreveu e não muda
     * nada no mundo real. As telas escondem os botões, mas a checagem tem que
     * viver aqui — esconder no front não é proteger.
     */
    protected function eventoAbertoDoOrganizador(int $id): Event
    {
        $evento = $this->eventoDoOrganizador($id);

        abort_if($evento->jaAconteceu(), 403, 'Este evento já aconteceu e não pode mais ser alterado.');

        return $evento;
    }
}
