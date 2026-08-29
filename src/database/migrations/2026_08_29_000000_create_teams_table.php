<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();

            // A equipe pertence ao ORGANIZADOR, não ao evento: a mesma assessoria
            // participa de vários eventos ao longo do ano.
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            // Aberta (true): aparece na lista para o atleta escolher sozinho.
            // Fechada (false): existe no sistema, mas o vínculo é decidido pelo
            // organizador — não aparece para o atleta.
            $table->boolean('is_public')->default(true);

            $table->boolean('active')->default(true);

            $table->timestamps();

            // Único por organizador, não global: dois organizadores podem ter
            // uma equipe com o mesmo nome sem colidir.
            $table->unique(['organizer_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
