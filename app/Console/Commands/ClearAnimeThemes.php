<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class ClearAnimeThemes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-anime-themes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all anime themes.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("Starting to clear all anime themes...");

        try {
            // Clear all anime descriptions
            DB::table('anime')->update(['themes' => null]);

            $this->info("All anime themes have been cleared.");
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
