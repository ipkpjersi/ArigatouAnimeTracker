<?php

namespace Database\Seeders;

use App\Models\WatchStatus;
use Illuminate\Database\Seeder;

class WatchStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WatchStatus::firstOrCreate(['status' => 'WATCHING']);
        WatchStatus::firstOrCreate(['status' => 'COMPLETED']);
        WatchStatus::firstOrCreate(['status' => 'ON-HOLD']);
        WatchStatus::firstOrCreate(['status' => 'DROPPED']);
        WatchStatus::firstOrCreate(['status' => 'PLAN-TO-WATCH']);
    }
}
