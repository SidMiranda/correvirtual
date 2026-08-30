<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Marca de "esta equipe tem brasão". O caminho do arquivo é
            // derivado do organizador e do id da equipe, então não há nome para
            // guardar — só precisamos saber se existe, porque com o CDN não dá
            // para perguntar ao disco (seria uma requisição de rede por linha).
            $table->boolean('has_logo')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('has_logo');
        });
    }
};
