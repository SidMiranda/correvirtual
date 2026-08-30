<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\EventKit;
use App\Models\EventModality;
use Illuminate\Http\Request;

/**
 * Visão geral de modalidades e kits, atravessando todos os eventos.
 *
 * Modalidade e kit pertencem a um evento — não existe "modalidade do
 * organizador". Mas obrigar a passar pelo evento toda vez que se quer olhar ou
 * cadastrar é chato, então estas telas dão o atalho pelo menu lateral: listam
 * tudo com a coluna do evento, e o botão de cadastrar pergunta em qual evento
 * antes de mandar para o formulário aninhado de sempre.
 */
class CatalogoController extends AdminController
{
    public function modalidades()
    {
        $modalities = EventModality::whereHas('event', $this->doOrganizador())
            ->with('event')
            ->join('events', 'events.id', '=', 'event_modalities.event_id')
            ->orderByDesc('events.event_date')
            ->orderBy('event_modalities.distance_km')
            ->select('event_modalities.*')
            ->paginate(30);

        return view('admin.modalities.geral', [
            'modalities' => $modalities,
            'eventos' => $this->eventosParaEscolha(),
        ]);
    }

    public function kits()
    {
        $kits = EventKit::whereHas('event', $this->doOrganizador())
            ->with('event')
            ->join('events', 'events.id', '=', 'event_kits.event_id')
            ->orderByDesc('events.event_date')
            ->orderBy('event_kits.price')
            ->select('event_kits.*')
            ->paginate(30);

        return view('admin.kits.geral', [
            'kits' => $kits,
            'eventos' => $this->eventosParaEscolha(),
        ]);
    }

    /**
     * Recebe o evento escolhido no seletor e manda para o formulário aninhado.
     *
     * Existe para o botão do menu lateral poder cadastrar sem que o usuário
     * tenha que achar o evento na lista antes. O evento é validado contra os
     * do organizador — o valor vem de um <select>, que é entrada do usuário
     * como qualquer outra.
     */
    public function novo(Request $request, string $tipo)
    {
        $eventoId = (int) $request->query('evento');

        $existe = Event::where('id', $eventoId)
            ->where('organizer_id', $this->organizerId())
            ->exists();

        if (!$existe) {
            return redirect()
                ->route($tipo === 'kits' ? 'admin.kits.geral' : 'admin.modalidades.geral')
                ->withErrors(['evento' => 'Escolha um evento seu antes de cadastrar.']);
        }

        return redirect()->route(
            $tipo === 'kits' ? 'admin.eventos.kits.create' : 'admin.eventos.modalidades.create',
            $eventoId
        );
    }

    private function doOrganizador(): callable
    {
        $organizerId = $this->organizerId();

        return fn ($q) => $q->where('organizer_id', $organizerId);
    }

    /** Eventos do organizador, o mais recente primeiro, para o seletor. */
    private function eventosParaEscolha()
    {
        return Event::where('organizer_id', $this->organizerId())
            ->orderByDesc('event_date')
            ->get(['id', 'title', 'event_date']);
    }
}
