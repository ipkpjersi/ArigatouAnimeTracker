<?php

namespace App\Console\Commands;

use App\Services\AnimeAdditionalDataImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DownloadAdditionalAnimeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-anime-additional-data {generateSqlFile?} {apiDescriptionsEmptyOnly?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches and inserts additional data for anime from the MyAnimeList (or notify.moe or kitsu.io) API. Optionally generates an importable SQL file. Optionally runs for API empty descriptions only (to force a retry of fetching descriptions).';

    /**
     * Execute the console command.
     */
    public function handle(AnimeAdditionalDataImportService $animeAdditionalDataImportService): void
    {
        $generateSqlFile = $this->argument('generateSqlFile') ?? false;
        $apiDescriptionsEmptyOnly = $this->argument('apiDescriptionsEmptyOnly') ?? false;

        $this->info('Starting to fetch additional anime data '.($generateSqlFile ? 'with generating an SQL file' : 'without generating an SQL file').'...');
        Log::channel('anime_import')->info('Starting to fetch additional anime data '.($generateSqlFile ? 'with generating an SQL file' : 'without generating an SQL file').'...');
        try {
            $logger = function ($message) {
                $this->info($message);
            };

            $result = $animeAdditionalDataImportService->downloadAdditionalAnimeData($logger, $generateSqlFile, $apiDescriptionsEmptyOnly);
            $duration = round($result['duration'], 2);

            $this->info("Fetched and updated additional anime data for {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds.");
            Log::channel('anime_import')->info("Fetched and updated additional anime data for {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds.");
        } catch (\Exception $e) {
            $this->error('An error occurred during the additional anime data fetch: '.$e);
            Log::channel('anime_import')->info('An error occurred during the additional anime data fetch: '.$e);
        }
    }
}
