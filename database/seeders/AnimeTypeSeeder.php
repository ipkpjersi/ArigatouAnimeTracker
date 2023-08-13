<?php

namespace Database\Seeders;

use App\Models\AnimeType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnimeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AnimeType::insert([
            ['type' => 'TV'],
            ['type' => 'MOVIE'],
            ['type' => 'OVA'],
            ['type' => 'ONA'],
            ['type' => 'SPECIAL'],
            ['type' => 'UNKNOWN'],
        ]);
    }
}
