<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

use function App\Helpers\rot19;

class AnimeImageDownloadService
{
    public function downloadImages($logger = null, $force = false)
    {
        if ($force) {
            DB::table('anime')->update(['image_downloaded' => false]);
            $logger && $logger("Force downloading: Resetting image_downloaded to false for all anime.");
        }
        $anime = DB::table('anime')
            ->whereNot('image_downloaded', '=', true)
            ->where(function ($query) {
                $query->whereNotNull('picture')
                    ->orWhereNotNull('thumbnail');
            })
            ->get();
        $downloaded = DB::table('anime')
            ->where('image_downloaded', '=', true)
            ->where(function ($query) {
                $query->whereNotNull('picture')
                    ->orWhereNotNull('thumbnail');
            })
            ->get();
        $startTime = microtime(true);
        $successful = 0;
        $imageFailed = 0;
        $total = $anime->count() + $downloaded->count();
        $remaining = $anime->count();
        $logger && $logger("Downloading images for $remaining out of $total anime.");
        foreach ($anime as $current) {
            $imageDownloaded = false;
            try {
                if ($current->thumbnail) {
                    if ($this->downloadImageFromUrl($current->thumbnail, 'thumbnail', $logger)) {
                        $successful++;
                        $imageDownloaded = true;
                    } else {
                        $imageFailed++;
                    }
                }
                if ($current->picture) {
                    if ($this->downloadImageFromUrl($current->picture, 'picture', $logger)) {
                        $successful++;
                        $imageDownloaded = true;
                    } else {
                        //If it failed to download the regular picture after downloading the thumbnail, force a re-download.
                        $imageDownloaded = false;
                        $imageFailed++;
                    }
                }
            } catch (\Exception $e) {
                $logger && $logger('An error occurred during downloading an image: '.$e);

                continue;
            }
            if ($imageDownloaded) {
                DB::table('anime')
                    ->where('id', $current->id)
                    ->limit(1)
                    ->update(['image_downloaded' => true]);
            }
            $sleepTime = rand(config('global.image_download_service_sleep_time_lower', 5), config('global.image_download_service_sleep_time_upper', 22));
            $logger && $logger("Sleeping for $sleepTime seconds");
            sleep($sleepTime);
        }
        $duration = microtime(true) - $startTime;

        return [
            'successful' => $successful,
            'total' => $total,
            'failed' => $imageFailed,
            'totalImages' => $successful + $imageFailed,
            'duration' => $duration,
        ];
    }

    private function downloadImageFromUrl($url, $type, $logger = null): bool
    {
        $filePath = $this->getFilePathFromUrl($url, $type);
        $fullPath = public_path($filePath);

        // Create the directories if they don't exist
        $directory = dirname($fullPath);
        if (! file_exists($directory)) {
            if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
        }

        // Check if the file already exists
        if (! file_exists($fullPath)) {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])->get($url);
            if ($response->successful()) {
                file_put_contents($fullPath, $response->body());
                $logger && $logger("Image $type downloaded successfully to: ".$fullPath);

                return true;
            } else {
                $logger && $logger("Failed to download $type image from: ".$url.' with response status: '.$response->status());
            }
        } else {
            $logger && $logger("Image $type already exists at: ".$fullPath);

            return true; //Technically it's downloaded, if we manually downloaded might as well mark it true.
        }

        return false;
    }

    private function getFilePathFromUrl($url, $type)
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];

        return "$type".$path;
    }

    public function zipImages()
    {
        $directories = [
            public_path('picture'),
            public_path('thumbnail'),
        ];

        foreach ($directories as $dir) {
            $this->createZipArchives($dir);
        }
    }

    private function createZipArchives($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($iterator as $path) {
            if ($path->isFile() && strtolower($path->getExtension()) !== 'zip') {
                $filePath = $path->getPathname();
                $fileNameWithoutExtension = pathinfo($filePath, PATHINFO_FILENAME);
                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                $rot19Filename = rot19($fileNameWithoutExtension).'.'.$fileExtension.'.zip';
                $zipPath = dirname($filePath).DIRECTORY_SEPARATOR.$rot19Filename;
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    //We probably don't want to maintain the subfolder inside the zips
                    //$zip->addFile($filePath, $iterator->getSubPathName());
                    //Add the file to the zip without subfolders
                    $zip->addFile($filePath, basename($filePath));
                    $zip->close();
                } else {
                    throw new \RuntimeException(sprintf('Failed to create ZIP archive: %s', $zipPath));
                }
            }
        }
    }

    public function unzipImages()
    {
        $directories = [
            public_path('picture'),
            public_path('thumbnail'),
        ];

        foreach ($directories as $dir) {
            $this->unzipInDirectory($dir);
        }
    }

    private function unzipInDirectory($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($iterator as $path) {
            if ($path->isFile() && strtolower($path->getExtension()) === 'zip') {
                $zipPath = $path->getPathname();
                $this->unzipImage($zipPath);
            }
        }
    }

    private function unzipImage($zipPath)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $extractPath = dirname($zipPath);  // Extract in the same directory where the zip file is located
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new \RuntimeException(sprintf('Failed to open ZIP archive: %s', $zipPath));
        }
    }
}
