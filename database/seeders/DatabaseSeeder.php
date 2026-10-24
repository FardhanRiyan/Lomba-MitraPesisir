<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Jalankan: php artisan db:seed  (aman diulang — idempoten). */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            FishSeeder::class,
            UserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
