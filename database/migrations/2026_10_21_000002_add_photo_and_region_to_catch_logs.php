<?php

use App\Enums\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catch_logs', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('user_id')->constrained('locations')->nullOnDelete();
            $table->enum('region', Region::values())->nullable()->after('location_id');
            $table->string('image_path', 500)->nullable()->after('note');
            $table->index(['location_id', 'fish_name']);
            $table->index(['region', 'fish_name']);
        });

        DB::table('catch_logs')
            ->join('users', 'users.id', '=', 'catch_logs.user_id')
            ->whereNull('catch_logs.location_id')
            ->selectRaw('catch_logs.id as id, users.location_id as location_id')
            ->orderBy('catch_logs.id')
            ->chunkById(100, function ($logs) {
                foreach ($logs as $log) {
                    DB::table('catch_logs')->where('id', $log->id)->update(['location_id' => $log->location_id]);
                }
            }, 'catch_logs.id', 'id');

        DB::table('catch_logs')
            ->join('locations', 'locations.id', '=', 'catch_logs.location_id')
            ->whereNull('catch_logs.region')
            ->selectRaw('catch_logs.id as id, locations.region as region')
            ->orderBy('catch_logs.id')
            ->chunkById(100, function ($logs) {
                foreach ($logs as $log) {
                    DB::table('catch_logs')->where('id', $log->id)->update(['region' => $log->region]);
                }
            }, 'catch_logs.id', 'id');
    }

    public function down(): void
    {
        Schema::table('catch_logs', function (Blueprint $table) {
            $table->dropIndex(['location_id', 'fish_name']);
            $table->dropIndex(['region', 'fish_name']);
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn('image_path');
            $table->dropColumn('region');
        });
    }
};
