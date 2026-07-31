<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['modality_id', 'kit_id']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('modality_id')
                ->after('event_id')
                ->constrained('event_modalities')
                ->restrictOnDelete();

            $table->foreignId('kit_id')
                ->after('modality_id')
                ->constrained('event_kits')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['modality_id']);
            $table->dropForeign(['kit_id']);
            $table->dropColumn(['modality_id', 'kit_id']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('modality_id')->after('event_id');
            $table->string('kit_id')->after('modality_id');
        });
    }
};
