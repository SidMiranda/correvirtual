<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\EventModality;
use Illuminate\Http\Request;

/**
 * Categorias (as distâncias) de um evento.
 *
 * "Categoria" aqui é a distância — 5km, 10km, Caminhada 3km. É o que o banco
 * chama de EventModality; o nome na tela é decisão do dono (2026-08-29). Não
 * existe categoria de premiação por faixa etária.
 */
class EventModalityController extends AdminController
{
    public function index(int $eventoId)
    {
        $event = $this->evento($eventoId);
        $modalities = $event->modalities()->orderBy('distance_km')->orderBy('name')->get();

        return view('admin.modalities.index', compact('event', 'modalities'));
    }

    public function create(int $eventoId)
    {
        $event = $this->evento($eventoId);

        return view('admin.modalities.create', compact('event'));
    }

    public function store(Request $request, int $eventoId)
    {
        $event = $this->evento($eventoId);

        $event->modalities()->create($this->validar($request));

        return redirect()
            ->route('admin.eventos.categorias.index', $event->id)
            ->with('sucesso', 'Categoria criada.');
    }

    public function edit(int $eventoId, int $id)
    {
        $event = $this->evento($eventoId);
        $modality = $this->modalidade($event, $id);

        return view('admin.modalities.edit', compact('event', 'modality'));
    }

    public function update(Request $request, int $eventoId, int $id)
    {
        $event = $this->evento($eventoId);
        $modality = $this->modalidade($event, $id);

        $modality->update($this->validar($request));

        return redirect()
            ->route('admin.eventos.categorias.index', $event->id)
            ->with('sucesso', 'Categoria atualizada.');
    }

    public function destroy(int $eventoId, int $id)
    {
        $event = $this->evento($eventoId);
        $modality = $this->modalidade($event, $id);

        // A foreign key de subscriptions.modality_id é restrictOnDelete, então o
        // banco recusaria — melhor explicar antes de deixar estourar erro 500.
        if ($modality->subscriptions()->exists()) {
            return back()->withErrors([
                'categoria' => 'Esta categoria já tem inscritos e por isso não pode ser apagada. Desative-a para tirá-la das novas inscrições.',
            ]);
        }

        $modality->delete();

        return redirect()
            ->route('admin.eventos.categorias.index', $event->id)
            ->with('sucesso', 'Categoria apagada.');
    }

    /** O evento, já filtrado pelo organizador do usuário logado. */
    private function evento(int $id): Event
    {
        return Event::where('id', $id)
            ->where('organizer_id', $this->organizerId())
            ->firstOrFail();
    }

    /** A categoria, obrigatoriamente dentro do evento já escopado acima. */
    private function modalidade(Event $event, int $id): EventModality
    {
        return $event->modalities()->where('id', $id)->firstOrFail();
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'active' => ['boolean'],
        ], [
            'max_participants.min' => 'O limite de vagas precisa ser pelo menos 1. Deixe em branco para não ter limite.',
        ]) + ['active' => $request->boolean('active')];
    }
}
