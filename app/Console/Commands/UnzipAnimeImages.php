<?php

namespace App\Console\Commands;

use App\Services\AnimeImageDownloadService;
use Illuminate\Console\Command;

class UnzipAnimeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:unzip-anime-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extracts zip archives for each anime image.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(AnimeImageDownloadService $animeImageDownloadService)
    {
        $this->info('Starting to unzip anime images...');

        try {
            $animeImageDownloadService->unzipImages();
            $this->info('Unzipping of anime images completed successfully.');
        } catch (\Exception $e) {
            $this->error('An error occurred during the unzipping process: '.$e);
        }
    }
}
