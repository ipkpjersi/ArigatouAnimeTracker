<?php

namespace App\Console\Commands;

use App\Services\AnimeImageDownloadService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:download-anime-images {--force}')]
#[Description('Downloads anime images for each anime.')]
class DownloadAnimeImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AnimeImageDownloadService $animeImageDownloadService): void
    {
        $this->info('Starting to download anime images...');
        Log::channel('anime_import')->info('Starting to download anime images...');

        try {
            $logger = function ($message) {
                $this->info($message);
            };

            $result = $animeImageDownloadService->downloadImages($logger, $this->option('force'));
            $duration = round($result['duration'], 2);
            $this->info("Downloaded {$result['successful']} out of {$result['totalImages']} images for {$result['total']} anime records successfully in {$duration} seconds.");
            Log::channel('anime_import')->info("Downloaded {$result['successful']} out of {$result['totalImages']} images for {$result['total']} anime records successfully in {$duration} seconds.");
            $this->call('app:zip-anime-images');
        } catch (\Exception $e) {
            $this->error('An error occurred during the anime image downloads fetch: '.$e);
            Log::channel('anime_import')->info('An error occurred during the anime image downloads fetch: '.$e);
        }
    }
}
