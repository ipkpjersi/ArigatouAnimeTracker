<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ClearAnimeImageZipFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-anime-image-zip-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes the zip files inside picture and thumbnail folders while retaining the folder structure.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting to clear image zip files...');

        $folders = [
            public_path('picture'),
            public_path('thumbnail'),
        ];

        foreach ($folders as $folder) {
            $this->deleteZipFiles($folder);
        }

        $this->info('Image zip files cleared.');
    }

    /**
     * Deletes zip files within the specified folder while retaining the folder structure.
     *
     * @param  string  $folder
     * @return void
     */
    private function deleteZipFiles($folder)
    {
        if (is_dir($folder)) {
            $it = new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if (! $file->isDir() && $this->isZipFile($file)) {
                    unlink($file->getRealPath());
                }
            }
        }
    }

    /**
     * Checks if a file is a zip file based on its extension.
     *
     * @param  \SplFileInfo  $file
     * @return bool
     */
    private function isZipFile($file)
    {
        return strtolower($file->getExtension()) === 'zip';
    }
}
