<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
