<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sponsor;
use App\Support\ImagemPublica;
use App\Support\ImagensDoPatrocinador;
use Illuminate\Http\Request;

/**
 * Patrocinadores do organizador.
 *
 * Como a equipe, o patrocinador pertence ao organizador e não ao evento: o
 * mesmo apoiador costuma cobrir várias provas ao longo do ano, e amarrá-lo a
 * um evento obrigaria a recadastrar a cada prova.
 *
 * A ordem de exibição é do organizador, não alfabética — quem aparece primeiro
 * é o que foi negociado no contrato.
 */
class SponsorController extends AdminController
{
    public function index()
    {
        $sponsors = Sponsor::where('organizer_id', $this->organizerId())
            ->orderBy('position')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        return view('admin.sponsors.create');
    }

    public function store(Request $request)
    {
        $sponsor = new Sponsor($this->validar($request));
        $sponsor->organizer_id = $this->organizerId();
        $sponsor->save();

        // Depois do save: o caminho do logo usa o id, que só existe agora.
        $this->guardarLogo($request, $sponsor);

        return redirect()
            ->route('admin.patrocinadores.index')
            ->with('sucesso', "Patrocinador \"{$sponsor->name}\" criado.");
    }

    public function edit(int $id)
    {
        $sponsor = $this->buscarDoOrganizador($id);

        return view('admin.sponsors.edit', compact('sponsor'));
    }

    public function update(Request $request, int $id)
    {
        $sponsor = $this->buscarDoOrganizador($id);

        $sponsor->fill($this->validar($request))->save();

        $this->guardarLogo($request, $sponsor);

        return redirect()
            ->route('admin.patrocinadores.index')
            ->with('sucesso', "Patrocinador \"{$sponsor->name}\" atualizado.");
    }

    public function destroy(int $id)
    {
        $sponsor = $this->buscarDoOrganizador($id);
        $nome = $sponsor->name;

        // O caminho no bucket é derivado do id: um patrocinador futuro com o
        // mesmo id herdaria este logo se ele ficasse órfão.
        ImagensDoPatrocinador::apagar($sponsor);

        $sponsor->delete();

        return redirect()
            ->route('admin.patrocinadores.index')
            ->with('sucesso', "Patrocinador \"{$nome}\" apagado.");
    }

    /**
     * 404 e não 403 para patrocinador de outro organizador: dizer "existe, mas
     * não é seu" já é contar algo sobre o vizinho.
     */
    private function buscarDoOrganizador(int $id): Sponsor
    {
        return Sponsor::where('id', $id)
            ->where('organizer_id', $this->organizerId())
            ->firstOrFail();
    }

    private function validar(Request $request): array
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // `url` exige esquema: sem isso, "mobspot.com.br" digitado sem o
            // https viraria link relativo e levaria para dentro do painel.
            'site_url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'logo' => ImagemPublica::regraDeValidacao(),
            'active' => ['boolean'],
        ], [
            'site_url.url' => 'O site precisa começar com https:// (ex.: https://mobspot.com.br).',
            'logo.image' => 'O logo precisa ser uma imagem (JPG, PNG ou WEBP).',
            'logo.max' => 'O logo passou de 5 MB.',
        ]);

        // O arquivo não é coluna do patrocinador — vai para o R2 em guardarLogo().
        unset($dados['logo']);

        return $dados + [
            'position' => (int) $request->input('position', 0),
            'active' => $request->boolean('active'),
        ];
    }

    /**
     * Sobe o logo, se veio um.
     *
     * `has_logo` é a marca de que existe: com o CDN não dá para perguntar ao
     * disco se o arquivo está lá. Mesma escolha já feita para a equipe.
     */
    private function guardarLogo(Request $request, Sponsor $sponsor): void
    {
        if (! $request->hasFile('logo')) {
            return;
        }

        ImagensDoPatrocinador::salvarLogo($sponsor, $request->file('logo'));

        $sponsor->has_logo = true;
        $sponsor->save();
    }
}
