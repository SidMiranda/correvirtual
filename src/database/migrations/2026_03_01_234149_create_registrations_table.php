<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos cruciais
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('event_modality_id')->constrained('event_modalities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Cupom é opcional, então pode ser nulo
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            
            // Dados financeiros e de status
            $table->decimal('amount', 10, 2); // Valor final calculado (com ou sem desconto)
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            
            $table->timestamp('registered_at')->useCurrent(); // Data exata em que o cara clicou em "Inscrever-se"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};