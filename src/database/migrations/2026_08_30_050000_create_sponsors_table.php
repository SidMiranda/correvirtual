<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();

            // O patrocinador é do ORGANIZADOR, não do evento: o mesmo apoiador
            // costuma cobrir várias provas ao longo do ano. Mesma escolha já
            // feita para equipes.
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();

            $table->string('name');

            // Quem patrocina quase sempre quer o clique de volta para a própria
            // casa — é metade do valor de aparecer ali.
            $table->string('site_url')->nullable();

            $table->text('description')->nullable();

            // A marca de que existe logo no bucket. Com o CDN não dá para
            // perguntar ao disco se o arquivo está lá (seria uma requisição de
            // rede por linha da listagem), então quem responde é o banco.
            $table->boolean('has_logo')->default(false);

            // Ordem de exibição: quem aparece primeiro é negociado no contrato,
            // não decidido por ordem alfabética.
            $table->integer('position')->default(0);

            $table->boolean('active')->default(true);

            $table->timestamps();

            // Sem slug, diferente de equipes: patrocinador não tem página nem
            // endereço próprio neste sistema — seria coluna sem uso.
            $table->index(['organizer_id', 'active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};
