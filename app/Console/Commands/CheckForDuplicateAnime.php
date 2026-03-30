<?php

namespace App\Console\Commands;

use App\Services\DuplicateAnimeService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:check-anime-duplicates')]
#[Description('Check for duplicate anime entries and export details to CSV.')]
class CheckForDuplicateAnime extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DuplicateAnimeService $duplicateAnimeService): void
    {
        $this->info('Starting the process of checking for duplicate anime entries.');

        $logger = function ($message) {
            $this->info($message);
        };
        $result = $duplicateAnimeService->exportDuplicatesToCSV($logger);

        $this->info("Process completed in {$result['duration']} seconds.");
        $this->info("CSV files are generated with timestamp: {$result['timestamp']}.");

        foreach ($result['exports'] as $exportType => $exportData) {
            $this->info("Export type {$exportType}:");
            $this->info(" - Exported records: {$exportData['count']}");
            $this->info(" - File path: storage/app/{$exportData['filePath']}");
        }
    }
}
