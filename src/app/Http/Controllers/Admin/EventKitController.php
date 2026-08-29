<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\EventKit;
use Illuminate\Http\Request;

/**
 * Kits de um evento — o que o atleta recebe e o que ele paga.
 *
 * O preço daqui é o valor cobrado de verdade no Pix: a inscrição copia
 * kit->price no momento em que é criada, e é esse valor que vai pro Mercado
 * Pago. Não existe mais sobreposição global de valor.
 */
class EventKitController extends AdminController
{
    public function index(int $eventoId)
    {
        $event = $this->evento($eventoId);
        $kits = $event->kits()->orderBy('price')->get();

        return view('admin.kits.index', compact('event', 'kits'));
    }

    public function create(int $eventoId)
    {
        $event = $this->evento($eventoId);

        return view('admin.kits.create', compact('event'));
    }

    public function store(Request $request, int $eventoId)
    {
        $event = $this->evento($eventoId);

        $event->kits()->create($this->validar($request));

        return redirect()
            ->route('admin.eventos.kits.index', $event->id)
            ->with('sucesso', 'Kit criado.');
    }

    public function edit(int $eventoId, int $id)
    {
        $event = $this->evento($eventoId);
        $kit = $this->kit($event, $id);

        return view('admin.kits.edit', compact('event', 'kit'));
    }

    public function update(Request $request, int $eventoId, int $id)
    {
        $event = $this->evento($eventoId);
        $kit = $this->kit($event, $id);

        $kit->update($this->validar($request));

        return redirect()
            ->route('admin.eventos.kits.index', $event->id)
            ->with('sucesso', 'Kit atualizado.');
    }

    public function destroy(int $eventoId, int $id)
    {
        $event = $this->evento($eventoId);
        $kit = $this->kit($event, $id);

        if ($kit->subscriptions()->exists()) {
            return back()->withErrors([
                'kit' => 'Este kit já foi escolhido por alguém e por isso não pode ser apagado. Desative-o para tirá-lo das novas inscrições.',
            ]);
        }

        $kit->delete();

        return redirect()
            ->route('admin.eventos.kits.index', $event->id)
            ->with('sucesso', 'Kit apagado.');
    }

    private function evento(int $id): Event
    {
        return Event::where('id', $id)
            ->where('organizer_id', $this->organizerId())
            ->firstOrFail();
    }

    private function kit(Event $event, int $id): EventKit
    {
        return $event->kits()->where('id', $id)->firstOrFail();
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            // min:0.01 e não min:0 — o Mercado Pago recusa cobrança de R$ 0,00,
            // e uma inscrição gratuita não deveria passar pelo fluxo de Pix.
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ], [
            'price.min' => 'O preço precisa ser de pelo menos R$ 0,01 — o Pix não aceita cobrança zerada.',
            'stock.min' => 'O estoque não pode ser negativo. Deixe em branco para não controlar estoque.',
        ]) + ['active' => $request->boolean('active')];
    }
}
