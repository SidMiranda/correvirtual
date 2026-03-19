<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;

class EventsController extends Controller
{
    public function show($event_id)
    {
        // Aqui você pode buscar os detalhes do evento usando o $event_id
        // Por exemplo, usando um modelo Event:
        // $event = Event::findOrFail($event_id);
        
        // Para este exemplo, vamos apenas retornar uma view com o ID do evento
        return view('events.event-details', ['event_id' => $event_id]);
    }
}