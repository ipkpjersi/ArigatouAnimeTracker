<?php

namespace App\Console\Commands;

use App\Services\AnimeAdditionalDataImportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:import-anime-additional-data')]
#[Description('Imports additional anime data from the generated SQL file')]
class ImportAdditionalAnimeData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AnimeAdditionalDataImportService $animeAdditionalDataImportService): void
    {
        $this->info('Starting to import additional anime data from SQL file...');
        Log::channel('anime_import')->info('Starting to import additional anime data from SQL file...');

        try {
            $logger = function ($message) {
                $this->info($message);
            };

            $result = $animeAdditionalDataImportService->importAdditionalAnimeData($logger);
            $duration = round($result['duration'], 2);

            $this->info("Imported additional data for {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds.");
            Log::channel('anime_import')->info("Imported additional data for {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds.");
        } catch (\Exception $e) {
            $this->error('An error occurred during the additional anime data fetch: '.$e);
            Log::channel('anime_import')->info('An error occurred during the additional anime data fetch: '.$e);
        }
    }
}
