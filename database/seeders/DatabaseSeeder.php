<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\ConsoleOutput;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            AnimeTypeSeeder::class,
            AnimeStatusSeeder::class,
            WatchStatusSeeder::class,
        ]);

        Artisan::call('app:import-anime-data', [], new ConsoleOutput);
    }
}
