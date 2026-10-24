<?php

use App\Enums\FishRarity;
use App\Enums\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fish', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->text('description');
            $table->enum('rarity', FishRarity::values());
            $table->enum('region', Region::values());
            $table->string('image_url', 500)->nullable();
            $table->timestamps();

            $table->unique(['name', 'region']);
            $table->index('region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fish');
    }
};
