<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearAnimeGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-anime-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all anime genres.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Starting to clear all anime genres...');

        try {
            // Clear all anime genres and api empty flag so all can be downloaded again
            DB::table('anime')->update(['genres' => null, 'api_descriptions_empty' => false]);

            $this->info('All anime genres have been cleared.');
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e);
        }
    }
}
