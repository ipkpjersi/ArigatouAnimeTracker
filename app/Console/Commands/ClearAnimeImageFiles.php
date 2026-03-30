<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

#[Signature('app:clear-anime-image-files')]
#[Description('Deletes the image files inside picture and thumbnail folders while retaining the folder structure.')]
class ClearAnimeImageFiles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting to clear image files...');

        $folders = [
            public_path('picture'),
            public_path('thumbnail'),
        ];

        foreach ($folders as $folder) {
            $this->deleteImageFiles($folder);
        }

        $this->info('Image files cleared.');
    }

    /**
     * Deletes image files within the specified folder while retaining the folder structure.
     *
     * @param  string  $folder
     * @return void
     */
    private function deleteImageFiles($folder)
    {
        if (is_dir($folder)) {
            $it = new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if (! $file->isDir() && $this->isImageFile($file)) {
                    unlink($file->getRealPath());
                }
            }
        }
    }

    /**
     * Checks if a file is an image file based on its extension.
     *
     * @param  \SplFileInfo  $file
     * @return bool
     */
    private function isImageFile($file)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff'];

        return in_array(strtolower($file->getExtension()), $imageExtensions);
    }
}
