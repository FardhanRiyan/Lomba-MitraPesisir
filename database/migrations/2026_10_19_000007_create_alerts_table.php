<?php

use App\Enums\AlertSource;
use App\Enums\TideLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->enum('source', array_column(AlertSource::cases(), 'value'));
            $table->enum('level', [TideLevel::WASPADA->value, TideLevel::BAHAYA->value]);
            $table->decimal('water_level', 4, 2)->nullable();
            $table->string('title', 150);
            $table->string('message', 500);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'location_id', 'source', 'created_at'], 'alerts_dedupe_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
