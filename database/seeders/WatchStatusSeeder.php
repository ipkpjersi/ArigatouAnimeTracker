<?php

namespace Database\Seeders;

use App\Models\WatchStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WatchStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WatchStatus::insert([
            ['status' => 'WATCHING'],
            ['status' => 'COMPLETED'],
            ['status' => 'ON-HOLD'],
            ['status' => 'DROPPED'],
            ['status' => 'PLAN-TO-WATCH'],
        ]);

    }
}
