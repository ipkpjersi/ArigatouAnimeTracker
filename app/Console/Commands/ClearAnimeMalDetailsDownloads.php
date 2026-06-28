<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:clear-anime-mal-details-downloads')]
#[Description('Clears the anime MAL details download flags so the additional data command will re-attempt fetching MAL stats (mean, rank, studios, etc.) for those anime.')]
class ClearAnimeMalDetailsDownloads extends Command
{
    public function handle(): void
    {
        $this->info('Starting to clear all anime MAL details download flags...');

        try {
            DB::table('anime')->update(['mal_details_downloaded' => false]);
            $this->info('All anime MAL details download flags have been cleared.');
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e);
        }
    }
}
