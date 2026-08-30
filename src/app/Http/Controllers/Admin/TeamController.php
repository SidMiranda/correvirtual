<?php

namespace App\Http\Controllers\Admin;

use App\Models\Team;
use App\Support\ImagemPublica;
use App\Support\ImagensDaEquipe;
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

        // Depois do save: o caminho do brasão usa o id, que só existe agora.
        $this->guardarBrasao($request, $team);

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

        $this->guardarBrasao($request, $team);

        return redirect()
            ->route('admin.equipes.index')
            ->with('sucesso', "Equipe \"{$team->name}\" atualizada.");
    }

    public function destroy(int $id)
    {
        $team = $this->buscarDoOrganizador($id);
        $nome = $team->name;

        // O caminho no bucket é derivado do id: uma equipe futura com o mesmo
        // id herdaria o brasão desta se ele ficasse órfão.
        ImagensDaEquipe::apagar($team);

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
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'brasao' => ImagemPublica::regraDeValidacao(),
            'is_public' => ['boolean'],
            'active' => ['boolean'],
        ], [
            'brasao.image' => 'O brasão precisa ser uma imagem (JPG, PNG ou WEBP).',
            'brasao.max' => 'O brasão passou de 5 MB.',
        ]);

        // O arquivo não é coluna da equipe — vai para o R2 em guardarBrasao().
        unset($dados['brasao']);

        return $dados + [
            'is_public' => $request->boolean('is_public'),
            'active' => $request->boolean('active'),
        ];
    }

    /**
     * Sobe o brasão, se veio um.
     *
     * `has_logo` é a marca de que existe: com o CDN não dá para perguntar ao
     * disco se o arquivo está lá (seria uma requisição de rede por linha da
     * listagem), então quem responde é o banco.
     */
    private function guardarBrasao(Request $request, Team $team): void
    {
        if (!$request->hasFile('brasao')) {
            return;
        }

        ImagensDaEquipe::salvarBrasao($team, $request->file('brasao'));

        $team->has_logo = true;
        $team->save();
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
