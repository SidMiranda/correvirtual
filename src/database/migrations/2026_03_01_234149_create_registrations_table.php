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

            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();

            $table->string('provider')->default('mercadopago');
            $table->string('transaction_id')->nullable();
            $table->string('payment_method')->default('pix');

            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded'])->default('pending');

            $table->text('qr_code')->nullable();
            $table->longText('qr_code_base64')->nullable();
            $table->string('ticket_url')->nullable();
            $table->dateTime('expires_at')->nullable();

            $table->dateTime('paid_at')->nullable();

            $table->json('payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};