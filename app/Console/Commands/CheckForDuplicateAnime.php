<?php

namespace App\Console\Commands;

use App\Services\DuplicateAnimeService;
use Illuminate\Console\Command;

class CheckForDuplicateAnime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-anime-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for duplicate anime entries and export details to CSV.';

    /**
     * Execute the console command.
     *
     * @param DuplicateAnimeService $duplicateAnimeService
     * @return void
     */
    public function handle(DuplicateAnimeService $duplicateAnimeService)
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
