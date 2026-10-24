<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('ADMIN','USER','NELAYAN','PEMBELI') NOT NULL DEFAULT 'USER'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('USER')->change();
            });
        }

        DB::table('users')->whereIn('role', ['NELAYAN', 'PEMBELI'])->update(['role' => 'USER']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('ADMIN','USER') NOT NULL DEFAULT 'USER'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('ADMIN','USER','NELAYAN','PEMBELI') NOT NULL DEFAULT 'USER'");
        }

        DB::table('users')->where('role', 'USER')->update(['role' => 'NELAYAN']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('ADMIN','NELAYAN','PEMBELI') NOT NULL DEFAULT 'PEMBELI'");
        }
    }
};
