<?php

namespace App\Console\Commands;

use App\Services\AnimeAdditionalDataImportService;
use App\Services\AnimeImageDownloadService;
use Illuminate\Console\Command;

class DownloadAnimeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-anime-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads anime images for each anime.';

    /**
     * Execute the console command.
     *
     * @param AnimeImageDownloadService $animeImageDownloadService
     * @return void
     */
    public function handle(AnimeImageDownloadService $animeImageDownloadService)
    {
        $this->info("Starting to download anime images...");

        try {
            $logger = function($message) {
                $this->info($message);
            };

            $result = $animeImageDownloadService->downloadImages($logger);
            $duration = round($result['duration'], 2);
            $this->info("Downloaded {$result['count']} out of {$result['totalImages']} images for {$result['total']} anime records successfully in {$duration} seconds.");
            $this->info("Starting to zip anime images...");
            $animeImageDownloadService->zipImages();
            $this->info("Zipping of anime images completed successfully.");
        } catch (\Exception $e) {
            $this->error('An error occurred during the anime image downloads fetch: ' . $e->getMessage());
        }
    }
}
