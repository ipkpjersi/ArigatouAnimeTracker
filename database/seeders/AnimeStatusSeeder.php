<?php

namespace Database\Seeders;

use App\Models\AnimeStatus;
use Illuminate\Database\Seeder;

class AnimeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AnimeStatus::firstOrCreate(['status' => 'FINISHED']);
        AnimeStatus::firstOrCreate(['status' => 'ONGOING']);
        AnimeStatus::firstOrCreate(['status' => 'UPCOMING']);
        AnimeStatus::firstOrCreate(['status' => 'UNKNOWN']);
    }
}
