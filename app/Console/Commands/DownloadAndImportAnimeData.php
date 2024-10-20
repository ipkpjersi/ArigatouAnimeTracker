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
    protected $description = 'Download and import anime data, then download additional data and images. This is the recommended import command.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting the process of downloading and importing anime data...');

        try {
            // Download and Import Anime Data
            $this->info('Downloading and importing anime data...');
            Artisan::call('app:import-anime-data', ['--forceDownload' => true, '--fullUpdate' => true], new ConsoleOutput);

            // Download Additional Anime Data for existing anime data (already has API description empty)
            $this->info('Downloading additional anime data for existing anime data...');
            Artisan::call('app:download-anime-additional-data', ['generateSqlFile' => true, 'apiDescriptionsEmptyOnly' => true], new ConsoleOutput);

            // Download Additional Anime Data for new anime data (does not have API description empty yet)
            $this->info('Downloading additional anime data for new anime data...');
            Artisan::call('app:download-anime-additional-data', ['generateSqlFile' => true, 'apiDescriptionsEmptyOnly' => false], new ConsoleOutput);

            // Download Anime Images
            $this->info('Downloading anime images...');
            //We could technically force this to re-download all anime images, except with our sleep timers it means it takes a week or more, so let's leave it off for now.
            //Artisan::call('app:download-anime-images', ['--force' => true], new ConsoleOutput);
            Artisan::call('app:download-anime-images', [], new ConsoleOutput);

            $this->info('All processes completed successfully.');

        } catch (\Exception $e) {
            $this->error('An error occurred during the process: '.$e);
        }
    }
}
