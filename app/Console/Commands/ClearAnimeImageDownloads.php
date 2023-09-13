<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class ClearAnimeImageDownloads extends Command
{
    protected $signature = 'app:clear-anime-image-downloads {deleteFiles : Whether to delete the actual files (true/false)}';
    protected $description = 'Clears the anime image download flags, optionally deleting the files.';

    public function handle()
    {
        $this->info("Starting to clear all anime image download flags...");

        $deleteFiles = $this->argument('deleteFiles') === 'true';

        try {
            // Clear all anime image download flags
            DB::table('anime')->update(['image_downloaded' => false]);

            if ($deleteFiles) {
                // Logic to delete files
                $folders = [
                    'public/picture/images/anime',
                    'public/picture/anime',
                    'public/picture/file',
                    'public/thumbnail/images/anime',
                    'public/thumbnail/anime',
                    'public/thumbnail/file',
                ];

                foreach ($folders as $folder) {
                    if (Storage::exists($folder)) {
                        Storage::deleteDirectory($folder);
                    }
                }

                $this->info("The actual image files have been deleted.");
            }

            $this->info("All anime image download flags have been cleared.");
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
