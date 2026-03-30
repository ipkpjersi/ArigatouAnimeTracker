<?php

namespace App\Console\Commands;

use App\Services\AnimeImageDownloadService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:unzip-anime-images')]
#[Description('Extracts zip archives for each anime image.')]
class UnzipAnimeImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AnimeImageDownloadService $animeImageDownloadService): void
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
