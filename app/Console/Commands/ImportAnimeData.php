<?php

namespace App\Console\Commands;

use App\Services\AnimeImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\ConsoleOutput;

class ImportAnimeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-anime-data {filePath?} {--forceDownload} {--skipBackup} {--fullUpdate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime from the anime database JSON file.';

    /**
     * Execute the console command.
     */
    public function handle(AnimeImportService $animeImportService): void
    {
        $this->info('Starting anime data import...');
        Log::channel('anime_import')->info('Starting anime data import...');

        $filePath = $this->argument('filePath') ?? storage_path('app/private/imports/anime-offline-database.json');
        $forceDownload = $this->option('forceDownload');
        $skipBackup = $this->option('skipBackup');
        $fullUpdate = $this->option('fullUpdate');
        try {
            $logger = function ($message) {
                $this->info($message);
            };

            // Perform a backup before doing anything, in case anything goes wrong.
            if (! $skipBackup) {
                $this->info('Backing up data before anime data import...');
                Log::channel('anime_import')->info('Backing up data before anime data import...');
                // This would be fine, but we might as well back up all the images etc too so everything matches.
                // Artisan::call('app:backup-database', [], new ConsoleOutput);
                Artisan::call('app:backup:run', [], new ConsoleOutput);
            }

            if ($forceDownload || ! file_exists($filePath)) {
                $this->info('Anime database file not found or force download is enabled. Downloading from source...');
                Log::channel('anime_import')->info('Anime database file not found or force download is enabled. Downloading from source...');
                $fileData = file_get_contents('https://raw.githubusercontent.com/manami-project/anime-offline-database/master/anime-offline-database.json');
                $directory = dirname($filePath);
                $this->info("Downloading anime import JSON file to $directory");
                if (! file_exists($directory)) {
                    if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
                    }
                }
                file_put_contents($filePath, $fileData);
            }

            $result = $animeImportService->importFromJsonFile($filePath, $fullUpdate, $logger);
            $duration = round($result['duration'], 2);

            $this->info("Imported {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds");
            Log::channel('anime_import')->info("Imported {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds");
        } catch (\Exception $e) {
            $this->error('An error occurred during anime data import: '.$e."\nStack Trace:\n".$e->getTraceAsString());
            Log::channel('anime_import')->info('An error occurred during anime data import: '.$e."\nStack Trace:\n".$e->getTraceAsString());
        }
    }
}
