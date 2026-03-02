<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_modalities', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o evento
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            
            $table->string('name'); // Ex: 5km, 10km, Caminhada 3km
            $table->decimal('distance_km', 5, 2)->nullable(); // Ex: 5.00
            
            $table->decimal('price', 10, 2); // Valor da inscrição
            
            $table->integer('max_participants')->nullable(); // Limite de vagas
            $table->boolean('active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_modalities');
    }
};