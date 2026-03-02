<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o organizador (se o organizador for apagado, os eventos dele também são)
            $table->foreignId('organizer_id')->constrained('organizers')->cascadeOnDelete();
            
            $table->string('title');
            $table->string('slug')->unique(); // Para a URL amigável (ex: correvirtual.com/carnarun-2026)
            $table->text('description')->nullable();
            $table->string('location');
            
            $table->dateTime('event_date');
            $table->dateTime('registration_deadline'); // Data limite para inscrições
            
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
