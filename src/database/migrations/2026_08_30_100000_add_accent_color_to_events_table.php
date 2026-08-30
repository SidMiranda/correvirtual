<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Cor de destaque do evento, em hexadecimal (#7f1d1d).
            //
            // Sem imagem enviada, o card e o banner viram um degradê escuro
            // puxado para esta cor, com o nome do evento por cima. Nula = usa o
            // azul escuro do tema do site.
            $table->string('accent_color', 7)->nullable()->after('banner_url');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('accent_color');
        });
    }
};
