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
                    ->whereNotNull('picture')
                    ->orWhereNotNull('thumbnail')
                    ->get();
        $startTime = microtime(true);
        $count = 0;
        $total = $anime->count();
        foreach ($anime as $current) {
            if ($current->thumbnail) {
                $this->downloadImageFromUrl($current->thumbnail, 'thumbnail', $logger);
            }
            if ($current->picture) {
                $this->downloadImageFromUrl($current->picture, 'picture', $logger);
            }
            sleep(5);
        }
        $duration = microtime(true) - $startTime;
        return [
            'count' => $count,
            'total' => $total,
            'duration' => $duration,
        ];
    }

    private function downloadImageFromUrl($url, $type, $logger = null)
    {
        $filePath = $this->getFilePathFromUrl($url, $type);
        $fullPath = public_path($filePath);

        // Create the directories if they don't exist
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Check if the file already exists
        if (!file_exists($fullPath)) {
            $response = Http::get($url);
            if ($response->successful()) {
                file_put_contents($fullPath, $response->body());
                $logger && $logger("Image $type downloaded successfully to: " . $fullPath);
            } else {
                $logger && $logger("Failed to download $type image from: " . $url);
            }
        } else {
            $logger && $logger("Image $type already exists at: " . $fullPath);
        }
    }

    private function getFilePathFromUrl($url, $type)
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];
        return "$type" . $path;
    }
}