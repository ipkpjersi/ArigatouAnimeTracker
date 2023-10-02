<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnimeImageDownloadService
{
    public function downloadImages($logger = null)
    {
        $anime = DB::table('anime')
                    ->whereNot("image_downloaded", "=", true)
                    ->where(function($query) {
                        $query->whereNotNull('picture')
                              ->orWhereNotNull('thumbnail');
                    })
                    ->get();
        $downloaded = DB::table('anime')
                    ->where("image_downloaded", "=", true)
                    ->where(function($query) {
                        $query->whereNotNull('picture')
                              ->orWhereNotNull('thumbnail');
                    })
                    ->get();
        $startTime = microtime(true);
        $count = 0;
        $imageFailed = 0;
        $total = $anime->count();
        $remaining = $total - $downloaded->count();
        $logger && $logger("Downloading images for $remaining out of $total anime.");
        foreach ($anime as $current) {
            $imageDownloaded = false;
            if ($current->thumbnail) {
                if ($this->downloadImageFromUrl($current->thumbnail, 'thumbnail', $logger)) {
                    $imageDownloaded = true;
                } else {
                    $imageFailed++;
                }
            }
            if ($current->picture) {
                if ($this->downloadImageFromUrl($current->picture, 'picture', $logger)) {
                    $imageDownloaded = true;
                } else {
                    $imageFailed++;
                }
            }
            if ($imageDownloaded) {
                DB::table('anime')
                  ->where('id', $current->id)
                  ->limit(1)
                  ->update(['image_downloaded' => true]);
            }
            $sleepTime = rand(config("global.image_download_service_sleep_time_lower", 5), config("global.image_download_service_sleep_time_upper", 22));
            $logger && $logger("Sleeping for $sleepTime seconds");
            sleep($sleepTime);
        }
        $duration = microtime(true) - $startTime;
        return [
            'count' => $count,
            'total' => $total,
            'failed' => $imageFailed,
            'totalImages' => $count + $imageFailed,
            'duration' => $duration,
        ];
    }

    private function downloadImageFromUrl($url, $type, $logger = null): bool
    {
        $filePath = $this->getFilePathFromUrl($url, $type);
        $fullPath = public_path($filePath);

        // Create the directories if they don't exist
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
        }

        // Check if the file already exists
        if (!file_exists($fullPath)) {
            $response = Http::get($url);
            if ($response->successful()) {
                file_put_contents($fullPath, $response->body());
                $logger && $logger("Image $type downloaded successfully to: " . $fullPath);
                return true;
            } else {
                $logger && $logger("Failed to download $type image from: " . $url . " with response status: " . $response->status());
            }
        } else {
            $logger && $logger("Image $type already exists at: " . $fullPath);
            return true; //Technically it's downloaded, if we manually downloaded might as well mark it true.
        }
        return false;
    }

    private function getFilePathFromUrl($url, $type)
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];
        return "$type" . $path;
    }
}
