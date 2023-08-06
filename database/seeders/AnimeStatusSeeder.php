<?php

namespace Database\Seeders;

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
        DB::table('anime_status')->insert([
            ['status' => 'FINISHED'],
            ['status' => 'ONGOING'],
            ['status' => 'UPCOMING'],
            ['status' => 'UNKNOWN'],
        ]);
    }
}
