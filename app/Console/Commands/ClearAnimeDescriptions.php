<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:clear-anime-descriptions')]
#[Description('Clears all anime descriptions.')]
class ClearAnimeDescriptions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting to clear all anime descriptions...');

        try {
            // Clear all anime descriptions and api empty flag so all can be downloaded again
            DB::table('anime')->update(['description' => null, 'api_descriptions_empty' => false]);

            $this->info('All anime descriptions have been cleared.');
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e);
        }
    }
}
