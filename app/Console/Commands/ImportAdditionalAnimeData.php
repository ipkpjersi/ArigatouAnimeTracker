<?php

namespace App\Console\Commands;

use App\Services\AnimeAdditionalDataImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportAdditionalAnimeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-anime-additional-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports additional anime data from the generated SQL file';

    /**
     * Execute the console command.
     *
     * @return void
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
