<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\AnimeStatus;
use App\Models\AnimeType;
use App\Services\AnimeImportService;
use Illuminate\Console\Command;

class ImportAnimeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-anime-data {filePath?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime from the anime database JSON file.';

    /**
     * Execute the console command.
     *
     * @param AnimeImportService $animeImportService
     * @return void
     */
    public function handle(AnimeImportService $animeImportService)
    {
        $this->info("Starting anime data import...");

        $filePath = $this->argument('filePath') ?? storage_path('app/imports/anime-offline-database.json');

        try {
            $logger = function($message) {
                $this->info($message);
            };

            $result = $animeImportService->importFromJsonFile($filePath, $logger);
            $duration = round($result['duration'], 2);

            $this->info("Imported {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds");
        } catch (\Exception $e) {
            $this->error('An error occurred during import: ' . $e->getMessage());
        }
    }
}
