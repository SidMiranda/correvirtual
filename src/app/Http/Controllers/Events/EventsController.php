<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventsController extends Controller
{
    public function index()
    {
        // Busca todos os eventos ativos, trazendo junto as modalidades e ordena pelas datas mais próximas
        $events = Event::with('modalities')
            ->where('active', true)
            ->orderBy('event_date', 'asc')
            ->get();

        // Retorna a view index passando a variável $events
        return view('index', compact('events'));
    }

    public function show($event_id)
    {
        // Aqui você pode buscar os detalhes do evento usando o $event_id
        // Por exemplo, usando um modelo Event:
        // $event = Event::findOrFail($event_id);

        // Para este exemplo, vamos apenas retornar uma view com o ID do evento
        return view('events.event-details', ['event_id' => $event_id]);
    }
}
