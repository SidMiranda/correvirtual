<?php

namespace App\Http\Controllers\Admin;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Equipes do organizador — assessorias, grupos de treino, times.
 *
 * A equipe pertence ao organizador, não ao evento: a mesma assessoria corre
 * vários eventos ao longo do ano.
 *
 * "Aberta" quer dizer que aparece na lista para o atleta escolher sozinho;
 * "fechada" existe no sistema mas o vínculo é decidido pelo organizador. Por
 * decisão do dono (2026-08-29), esta rodada é só o cadastro no painel — a
 * escolha na inscrição do atleta não foi tocada.
 */
class TeamController extends AdminController
{
    public function index()
    {
        $teams = Team::where('organizer_id', $this->organizerId())
            ->orderBy('name')
            ->paginate(20);

        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        return view('admin.teams.create');
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);

        $team = new Team($dados);
        $team->organizer_id = $this->organizerId();
        $team->slug = $this->slugUnico($dados['name']);
        $team->save();

        return redirect()
            ->route('admin.equipes.index')
            ->with('sucesso', "Equipe \"{$team->name}\" criada.");
    }

    public function edit(int $id)
    {
        $team = $this->buscarDoOrganizador($id);

        return view('admin.teams.edit', compact('team'));
    }

    public function update(Request $request, int $id)
    {
        $team = $this->buscarDoOrganizador($id);
        $dados = $this->validar($request);

        if ($dados['name'] !== $team->name) {
            $team->slug = $this->slugUnico($dados['name'], $team->id);
        }

        $team->fill($dados)->save();

        return redirect()
            ->route('admin.equipes.index')
            ->with('sucesso', "Equipe \"{$team->name}\" atualizada.");
    }

    public function destroy(int $id)
    {
        $team = $this->buscarDoOrganizador($id);
        $nome = $team->name;
        $team->delete();

        return redirect()
            ->route('admin.equipes.index')
            ->with('sucesso', "Equipe \"{$nome}\" apagada.");
    }

    private function buscarDoOrganizador(int $id): Team
    {
        return Team::where('id', $id)
            ->where('organizer_id', $this->organizerId())
            ->firstOrFail();
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['boolean'],
            'active' => ['boolean'],
        ]) + [
            'is_public' => $request->boolean('is_public'),
            'active' => $request->boolean('active'),
        ];
    }

    /** O slug é único por organizador, não global (ver a migration). */
    private function slugUnico(string $nome, ?int $ignorarId = null): string
    {
        $base = Str::slug($nome);
        $slug = $base;
        $n = 2;

        while (Team::where('organizer_id', $this->organizerId())
            ->where('slug', $slug)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }
}
