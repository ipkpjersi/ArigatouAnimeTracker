<?php

namespace Database\Seeders;

use App\Models\AnimeType;
use Illuminate\Database\Seeder;

class AnimeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnimeType::firstOrCreate(['type' => 'TV']);
        AnimeType::firstOrCreate(['type' => 'MOVIE']);
        AnimeType::firstOrCreate(['type' => 'OVA']);
        AnimeType::firstOrCreate(['type' => 'ONA']);
        AnimeType::firstOrCreate(['type' => 'SPECIAL']);
        AnimeType::firstOrCreate(['type' => 'UNKNOWN']);
    }
}
