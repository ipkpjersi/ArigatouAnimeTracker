<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ClearAnimeImageFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-anime-image-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes the image files inside picture and thumbnail folders while retaining the folder structure.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("Starting to clear image files...");

        $folders = [
            public_path('picture'),
            public_path('thumbnail')
        ];

        foreach ($folders as $folder) {
            $this->deleteImageFiles($folder);
        }

        $this->info("Image files cleared.");
    }

    /**
     * Deletes image files within the specified folder while retaining the folder structure.
     *
     * @param string $folder
     * @return void
     */
    private function deleteImageFiles($folder)
    {
        if (is_dir($folder)) {
            $it = new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if (!$file->isDir() && $this->isImageFile($file)) {
                    unlink($file->getRealPath());
                }
            }
        }
    }

    /**
     * Checks if a file is an image file based on its extension.
     *
     * @param \SplFileInfo $file
     * @return bool
     */
    private function isImageFile($file)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff'];
        return in_array(strtolower($file->getExtension()), $imageExtensions);
    }
}
