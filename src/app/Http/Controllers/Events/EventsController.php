<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\Arquivos;

class EventsController extends Controller
{
    public function index()
    {
        $organizerId = app('currentOrganizer')->id;

        $events = Event::with('modalities')
            ->where('active', true)
            ->where('organizer_id', $organizerId)
            ->get();

        // Duas listas, com ordens opostas de propósito: o que ainda vai
        // acontecer sobe pelo mais próximo (é onde o atleta se inscreve), e o
        // que já passou desce pelo mais recente (é histórico — a prova do ano
        // passado interessa mais que a de cinco anos atrás).
        $proximosEventos = $events
            ->filter(fn ($e) => !$e->jaAconteceu())
            ->sortBy('event_date')
            ->values();

        $eventosPassados = $events
            ->filter(fn ($e) => $e->jaAconteceu())
            ->sortByDesc('event_date')
            ->values();

        return view('index', compact('proximosEventos', 'eventosPassados'));
    }

    public function show($event_id)
    {
        $organizerId = app('currentOrganizer')->id;

        // Busca o evento pelo ID e já carrega as modalidades e os kits associados
        $event = Event::with(['modalities', 'kits'])
            ->where('organizer_id', $organizerId)
            ->findOrFail($event_id);

        // Cartão de pré-visualização do link: quando alguém manda a página deste
        // evento no WhatsApp, é a corrida que precisa aparecer — não a capa
        // genérica do organizador.
        $og = [
            'tipo' => 'article',
            'titulo' => $event->title . ' — ' . $event->event_date?->format('d/m/Y'),
            'descricao' => $event->location . '. ' . $event->description,
            'imagem' => Arquivos::bannerDoEvento($event),
        ];

        return view('events.event-details', compact('event', 'og'));
    }
}
