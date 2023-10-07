<?php

namespace App\Console\Commands;

use App\Services\AnimeImageDownloadService;
use Illuminate\Console\Command;

class ZipAnimeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:zip-anime-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates zip archives for each anime image.';

    /**
     * Execute the console command.
     *
     * @param AnimeImageDownloadService $animeImageDownloadService
     * @return void
     */
    public function handle(AnimeImageDownloadService $animeImageDownloadService)
    {
        $this->info("Starting to zip anime images...");

        try {
            $animeImageDownloadService->zipImages();
            $this->info("Zipping of anime images completed successfully.");
        } catch (\Exception $e) {
            $this->error('An error occurred during the zipping process: ' . $e->getMessage());
        }
    }
}
