<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventsController extends Controller
{
    public function index()
    {
        $organizerId = app('currentOrganizer')->id;

        // Busca todos os eventos ativos, trazendo junto as modalidades e ordena pelas datas mais próximas
        $events = Event::with('modalities')
            ->where('active', true)
            ->where('organizer_id', $organizerId)
            ->orderBy('event_date', 'asc')
            ->get();

        // Retorna a view index passando a variável $events
        return view('index', compact('events'));
    }

    public function show($event_id)
    {
        $organizerId = app('currentOrganizer')->id;

        // Busca o evento pelo ID e já carrega as modalidades e os kits associados
        $event = Event::with(['modalities', 'kits'])
            ->where('organizer_id', $organizerId)
            ->findOrFail($event_id);

        return view('events.event-details', compact('event'));
    }
}
