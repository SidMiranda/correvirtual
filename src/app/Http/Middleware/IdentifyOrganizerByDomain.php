<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organizer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

class IdentifyOrganizerByDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pega o domínio da requisição (ex: correvirtual.com.br ou meuevento.com)
        $domain = $request->getHost();

        // 2. Verifica se estamos no ambiente local (localhost ou IP da rede Wi-Fi)
        $isLocalNetwork = str_starts_with($domain, '192.168.') || str_starts_with($domain, '10.');

        if (app()->environment('local') && (in_array($domain, ['localhost', '127.0.0.1']) || $isLocalNetwork)) {
            // Pega o segundo organizador do banco automaticamente para facilitar os testes locais
            // $organizer = Organizer::skip(1)->first();
            // Pega o primeiro organizador do banco automaticamente para facilitar os testes locais
            $organizer = Organizer::first();
        } else {
            // Busca o organizador no banco de dados baseado no domínio real
            $organizer = Organizer::where('domain', $domain)->first();
        }

        if (!$organizer) {
            // Se o domínio não existir no seu banco, retorna erro ou redireciona
            abort(404, 'Organizador não encontrado para este domínio.');
        }

        // 3. Compartilha o organizador globalmente na memória do Laravel (Service Container)
        App::instance('currentOrganizer', $organizer);

        // 4. Compartilha a variável globalmente com TODAS as views (Blade)
        View::share('organizerName', $organizer->name);
        View::share('organizerId', $organizer->id);
        View::share('organizerEmail', $organizer->email);

        // (Opcional) Adiciona ao request para facilitar o uso nos controllers
        $request->merge(['current_organizer_id' => $organizer->id]);

        return $next($request);
    }
}
