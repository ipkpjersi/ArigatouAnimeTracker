<?php

namespace Database\Seeders;

use App\Models\AnimeStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnimeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AnimeStatus::insert([
            ['status' => 'FINISHED'],
            ['status' => 'ONGOING'],
            ['status' => 'UPCOMING'],
            ['status' => 'UNKNOWN'],
        ]);
    }
}
