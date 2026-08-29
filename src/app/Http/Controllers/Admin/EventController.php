<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends AdminController
{
    public function index()
    {
        $events = Event::where('organizer_id', $this->organizerId())
            ->withCount(['modalities', 'kits', 'subscriptions'])
            ->orderByDesc('event_date')
            ->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);

        $evento = new Event($dados);
        $evento->organizer_id = $this->organizerId();
        $evento->slug = $this->slugUnico($dados['title']);
        $evento->save();

        return redirect()
            ->route('admin.eventos.index')
            ->with('sucesso', "Evento \"{$evento->title}\" criado.");
    }

    public function edit(int $id)
    {
        $event = $this->buscarDoOrganizador($id);

        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, int $id)
    {
        $event = $this->buscarDoOrganizador($id);
        $dados = $this->validar($request);

        // O slug só é refeito se o título mudou — links já divulgados de um
        // evento existente não podem quebrar porque alguém corrigiu um acento.
        if ($dados['title'] !== $event->title) {
            $event->slug = $this->slugUnico($dados['title'], $event->id);
        }

        $event->fill($dados)->save();

        return redirect()
            ->route('admin.eventos.index')
            ->with('sucesso', "Evento \"{$event->title}\" atualizado.");
    }

    public function destroy(int $id)
    {
        $event = $this->buscarDoOrganizador($id);

        // Apagar um evento derruba em cascata modalidades, kits e inscrições —
        // inclusive inscrição paga. Se alguém já se inscreveu, o caminho é
        // desativar (some do site público, o histórico continua de pé).
        if ($event->subscriptions()->exists()) {
            return back()->withErrors([
                'evento' => 'Este evento já tem inscrições e por isso não pode ser apagado. Desative-o para tirá-lo do ar sem perder o histórico.',
            ]);
        }

        $titulo = $event->title;
        $event->delete();

        return redirect()
            ->route('admin.eventos.index')
            ->with('sucesso', "Evento \"{$titulo}\" apagado.");
    }

    /**
     * Busca o evento JÁ filtrando pelo organizador do usuário.
     *
     * 404 e não 403 de propósito: um organizador não deve nem descobrir que o
     * evento de outro existe. Nunca buscar por ID solto e conferir depois —
     * é assim que nasce o vazamento entre clientes (ver BUG-005).
     */
    private function buscarDoOrganizador(int $id): Event
    {
        return Event::where('id', $id)
            ->where('organizer_id', $this->organizerId())
            ->firstOrFail();
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'registration_deadline' => ['required', 'date', 'before_or_equal:event_date'],
            'banner_url' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ], [
            'registration_deadline.before_or_equal' => 'O prazo de inscrição não pode ser depois da data do evento.',
        ]) + ['active' => $request->boolean('active')];
    }

    /**
     * `events.slug` é único no banco inteiro (não por organizador), então dois
     * organizadores com "Corrida de Verão" colidiriam. Acrescenta sufixo até
     * achar um livre.
     */
    private function slugUnico(string $titulo, ?int $ignorarId = null): string
    {
        $base = Str::slug($titulo);
        $slug = $base;
        $n = 2;

        while (Event::where('slug', $slug)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }
}
