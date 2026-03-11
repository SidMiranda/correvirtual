<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o evento
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            
            $table->string('code')->unique(); // O código do cupom que o usuário digita
            $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed'); // Porcentagem ou valor fixo
            $table->decimal('discount_value', 10, 2); // Ex: 10.00 (10 reais ou 10%)
            $table->decimal('minimum_amount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable(); // Quantas vezes pode ser usado no total (nulo = ilimitado)
            $table->integer('used_count')->default(0); // Quantas vezes já foi usado
            
            $table->boolean('active')->default(true);
            $table->dateTime('expires_at')->nullable(); // Data de validade do cupom
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};