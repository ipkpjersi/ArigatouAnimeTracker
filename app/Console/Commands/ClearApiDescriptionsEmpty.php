<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:clear-anime-api-descriptions-empty')]
#[Description('Clears all API descriptions empty flags.')]
class ClearApiDescriptionsEmpty extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
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
