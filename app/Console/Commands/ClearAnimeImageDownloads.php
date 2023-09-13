<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class ClearAnimeImageDownloads extends Command
{
    protected $signature = 'app:clear-anime-image-downloads {deleteFiles=false : Whether to delete the actual files (true/false)}';
    protected $description = 'Clears the anime image download flags, optionally deleting the files.';

    public function handle()
    {
        $this->info("Starting to clear all anime image download flags...");

        $deleteFiles = $this->argument('deleteFiles') === 'true';

        try {
            // Clear all anime image download flags
            DB::table('anime')->update(['image_downloaded' => false]);

            if ($deleteFiles) {
                $folders = [
                    'picture/images/anime',
                    'picture/anime',
                    'picture/file',
                    'thumbnail/images/anime',
                    'thumbnail/anime',
                    'thumbnail/file'
                ];
                foreach ($folders as $folder) {
                    $dir = public_path($folder);
                    if (is_dir($dir)) {
                        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
                        $files = new \RecursiveIteratorIterator($it,
                                    \RecursiveIteratorIterator::CHILD_FIRST);
                        foreach($files as $file) {
                            if ($file->isDir()){
                                rmdir($file->getRealPath());
                            } else {
                                unlink($file->getRealPath());
                            }
                        }
                        rmdir($dir);
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
