<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\ConsoleOutput;

class DownloadAndImportAnimeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-and-import-anime-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and import anime data, then download additional data and images.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("Starting the process of downloading and importing anime data...");

        try {
            // Download and Import Anime Data
            $this->info("Downloading and importing anime data...");
            Artisan::call('app:import-anime-data', ['--forceDownload'], new ConsoleOutput);

            // Download Additional Anime Data
            $this->info("Downloading additional anime data...");
            Artisan::call('app:download-anime-additional-data', [true], new ConsoleOutput);

            // Download Anime Images
            $this->info("Downloading anime images...");
            Artisan::call('app:download-anime-images', [], new ConsoleOutput);

            $this->info("All processes completed successfully.");

        } catch (\Exception $e) {
            $this->error('An error occurred during the process: ' . $e->getMessage());
        }
    }
}
