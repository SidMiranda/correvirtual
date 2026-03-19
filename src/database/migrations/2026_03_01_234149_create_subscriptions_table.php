<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {

            $table->id();

            // evento
            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();

            // atleta
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // modalidade escolhida
            $table->string('modality_id');
            // ex: 3k_walk, 5k_run, 10k_run

            // kit escolhido
            $table->string('kit_id');

            // preço no momento da inscrição
            $table->decimal('price', 8, 2);

            // número de peito (gerado depois do pagamento)
            $table->integer('bib_number')->nullable();

            // status da inscrição
            $table->enum('status', [
                'pending',   // aguardando pagamento
                'paid',      // pago
                'cancelled'
            ])->default('pending');

            // quando foi confirmado
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            // evita inscrição duplicada no mesmo evento
            $table->unique(['event_id', 'user_id']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
