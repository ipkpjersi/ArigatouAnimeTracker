<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearApiDescriptionsEmpty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-anime-api-descriptions-empty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all API descriptions empty flags.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Starting to clear all anime API descriptions empty flags...');

        try {
            // Clear all anime genres and api empty flag so all can be downloaded again
            DB::table('anime')->update(['api_descriptions_empty' => false]);

            $this->info('All anime API descriptions empty flags have been cleared.');
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e);
        }
    }
}
