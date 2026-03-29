<?php

namespace App\Console\Commands;

use App\Services\AnimeImageDownloadService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:zip-anime-images')]
#[Description('Creates zip archives for each anime image.')]
class ZipAnimeImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AnimeImageDownloadService $animeImageDownloadService): void
    {
        $this->info('Starting to zip anime images...');
        try {
            $animeImageDownloadService->zipImages();
            $this->info('Zipping of anime images completed successfully.');
        } catch (\Exception $e) {
            $this->error('An error occurred during the zipping process: '.$e);
        }
    }
}
