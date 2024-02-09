<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class ClearAnimeDescriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-anime-descriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all anime descriptions.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("Starting to clear all anime descriptions...");

        try {
            // Clear all anime descriptions and api empty flag so all can be downloaded again
            DB::table('anime')->update(['description' => null, 'api_descriptions_empty' => false]);

            $this->info("All anime descriptions have been cleared.");
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e);
        }
    }
}
