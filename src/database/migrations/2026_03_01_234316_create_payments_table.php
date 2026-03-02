<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com a inscrição
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            
            // Dados do gateway de pagamento
            $table->string('provider')->default('mercadopago');
            $table->string('transaction_id')->nullable(); // O ID externo que o Mercado Pago vai nos devolver
            $table->string('payment_method')->default('pix');
            
            // Controle de status financeiro
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded'])->default('pending');
            $table->dateTime('paid_at')->nullable(); // Data exata em que o PIX caiu
            
            // Auditoria (salva o retorno completo do Mercado Pago para caso dê algum problema no futuro)
            $table->json('payload')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};