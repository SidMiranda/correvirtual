<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\Subscription;

class DashboardController extends AdminController
{
    public function index()
    {
        $organizerId = $this->organizerId();

        $eventos = Event::where('organizer_id', $organizerId);

        // Inscrições chegam pelo evento — `subscriptions` não tem organizer_id
        // próprio, e não vai ter: o vínculo com o organizador é o evento.
        $inscricoes = Subscription::whereHas('event', fn ($q) => $q->where('organizer_id', $organizerId));

        $resumo = [
            'eventos' => (clone $eventos)->count(),
            'eventos_ativos' => (clone $eventos)->where('active', true)->count(),
            'inscricoes' => (clone $inscricoes)->count(),
            'inscricoes_pagas' => (clone $inscricoes)->where('status', 'paid')->count(),
        ];

        $proximos = Event::where('organizer_id', $organizerId)
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('resumo', 'proximos'));
    }
}
