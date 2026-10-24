<?php

use App\Enums\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->enum('region', Region::values());
            $table->timestamps();

            $table->index('region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
