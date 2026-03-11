<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_kits', function (Blueprint $table) {
            $table->id();

            // Relacionamento com evento
            $table->foreignId('event_id')
                  ->constrained('events')
                  ->cascadeOnDelete();

            // Dados do kit
            $table->string('name'); // Ex: Kit Digital, Kit Medalha, Kit Camisa
            $table->text('description')->nullable();

            // Valor
            $table->decimal('price', 10, 2);

            // Controle de estoque (opcional)
            $table->integer('stock')->nullable(); 
            $table->integer('sold')->default(0);

            // Controle de status
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_kits');
    }
};
