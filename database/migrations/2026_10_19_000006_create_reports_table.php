<?php

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('title', 150);
            $table->enum('category', ReportCategory::values());
            $table->enum('severity', ReportSeverity::values());
            $table->enum('status', ReportStatus::values())->default(ReportStatus::BARU->value);
            $table->text('description');
            $table->timestamps();

            $table->index('status');
            $table->index('category');
            $table->index('severity');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
