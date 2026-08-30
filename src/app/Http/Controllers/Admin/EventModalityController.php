<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\EventModality;
use Illuminate\Http\Request;

/**
 * Modalidades (as distâncias) de um evento.
 *
 * "Modalidade" aqui é a distância — 5km, 10km, Caminhada 3km. É o que o banco
 * chama de EventModality; o nome na tela é decisão do dono (2026-08-29). Não
 * existe modalidade de premiação por faixa etária.
 */
class EventModalityController extends AdminController
{
    public function index(int $eventoId)
    {
        // Listar continua valendo para evento já realizado: a tela vira só
        // leitura, sem os botões de criar, editar e apagar.
        $event = $this->eventoDoOrganizador($eventoId);
        $modalities = $event->modalities()->orderBy('distance_km')->orderBy('name')->get();

        return view('admin.modalities.index', compact('event', 'modalities'));
    }

    public function create(int $eventoId)
    {
        $event = $this->eventoAbertoDoOrganizador($eventoId);

        return view('admin.modalities.create', compact('event'));
    }

    public function store(Request $request, int $eventoId)
    {
        $event = $this->eventoAbertoDoOrganizador($eventoId);

        $event->modalities()->create($this->validar($request));

        return redirect()
            ->route('admin.eventos.modalidades.index', $event->id)
            ->with('sucesso', 'Modalidade criada.');
    }

    public function edit(int $eventoId, int $id)
    {
        $event = $this->eventoAbertoDoOrganizador($eventoId);
        $modality = $this->modalidade($event, $id);

        return view('admin.modalities.edit', compact('event', 'modality'));
    }

    public function update(Request $request, int $eventoId, int $id)
    {
        $event = $this->eventoAbertoDoOrganizador($eventoId);
        $modality = $this->modalidade($event, $id);

        $modality->update($this->validar($request));

        return redirect()
            ->route('admin.eventos.modalidades.index', $event->id)
            ->with('sucesso', 'Modalidade atualizada.');
    }

    public function destroy(int $eventoId, int $id)
    {
        $event = $this->eventoAbertoDoOrganizador($eventoId);
        $modality = $this->modalidade($event, $id);

        // A foreign key de subscriptions.modality_id é restrictOnDelete, então o
        // banco recusaria — melhor explicar antes de deixar estourar erro 500.
        if ($modality->subscriptions()->exists()) {
            return back()->withErrors([
                'modalidade' => 'Esta modalidade já tem inscritos e por isso não pode ser apagada. Desative-a para tirá-la das novas inscrições.',
            ]);
        }

        $modality->delete();

        return redirect()
            ->route('admin.eventos.modalidades.index', $event->id)
            ->with('sucesso', 'Modalidade apagada.');
    }


    /** A modalidade, obrigatoriamente dentro do evento já escopado acima. */
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
