<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:clear-anime-mal-details-empty')]
#[Description('Clears all anime MAL details empty flags so the additional data command will retry fetching MAL details (mean, rank, studios, etc.) for those anime from the normal pass again.')]
class ClearAnimeMalDetailsEmpty extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting to clear all anime MAL details empty flags...');

        try {
            DB::table('anime')->update(['mal_details_empty' => false]);

            $this->info('All anime MAL details empty flags have been cleared.');
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e);
        }
    }
}
