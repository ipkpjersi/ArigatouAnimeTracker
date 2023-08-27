<?php

namespace App\Console\Commands;

use App\Services\AnimeAdditionalDataImportService;
use Illuminate\Console\Command;

class FetchAdditionalAnimeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-anime-additional-data {generateSqlFile?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches additional data for anime from the MyAnimeList API.';

    /**
     * Execute the console command.
     *
     * @param AnimeAdditionalDataImportService $animeAdditionalDataImportService
     * @return void
     */
    public function handle(AnimeAdditionalDataImportService $animeAdditionalDataImportService)
    {
        $this->info("Starting to fetch additional anime data...");

        $generateSqlFile = $this->argument('generateSqlFile') ?? false;

        try {
            $logger = function($message) {
                $this->info($message);
            };

            $result = $animeAdditionalDataImportService->fetchAdditionalAnimeData($logger, $generateSqlFile);
            $duration = round($result['duration'], 2);

            $this->info("Fetched additional data for {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds.");
        } catch (\Exception $e) {
            $this->error('An error occurred during the data fetch: ' . $e->getMessage());
        }
    }
}
