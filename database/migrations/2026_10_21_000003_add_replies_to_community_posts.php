<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('community_posts')->cascadeOnDelete();
            $table->index(['parent_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'created_at']);
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
