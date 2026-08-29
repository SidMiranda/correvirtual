<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Porta de entrada do painel administrativo.
 *
 * Exige duas coisas, não uma: o papel `organizer_admin` E um `organizer_id`
 * preenchido. O papel sozinho não basta porque todo o escopo do painel sai do
 * organizador do usuário — um admin sem organizador enxergaria consultas sem
 * filtro nenhum. Ver docs/specs/painel-admin.md.
 */
class EnsureOrganizerAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'organizer_admin' || !$user->organizer_id) {
            abort(403, 'Acesso restrito aos administradores do organizador.');
        }

        return $next($request);
    }
}
