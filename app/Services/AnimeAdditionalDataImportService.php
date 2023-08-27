<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnimeAdditionalDataImportService
{
    public function fetchAdditionalAnimeData($logger = null, $generateSqlFile = false)
    {
        $startTime = microtime(true);
        $count = 0;
        $animes = DB::table('anime')
                    ->whereNull('description')
                    ->orWhereNull('genres')
                    ->get();
        $total = count($animes);

        $sqlFile = $generateSqlFile ? fopen(('database/seeders/anime_additional_data.sql'), 'a') : null;

        foreach ($animes as $anime) {
            $malId = null;
            $notifyMoeId = null;
            $kitsuId = null;
            if (isset($anime->sources)) {
                $sources = explode(',', $anime->sources);
                foreach ($sources as $source) {
                    if (strpos($source, 'myanimelist.net/anime/') !== false) {
                        $malId = explode('/', rtrim($source, '/'))[4];
                    }
                    if (strpos($source, 'notify.moe/anime/') !== false) {
                        $notifyMoeId = explode('/', rtrim($source, '/'))[4];
                    }
                    if (strpos($source, 'kitsu.io/anime/') !== false) {
                        $kitsuId = explode('/', rtrim($source, '/'))[4];
                    }
                }
            }

            $description = null;
            $genres = null;

            // Try MAL first
            if ($malId) {
                $response = Http::withHeaders([
                    'X-MAL-CLIENT-ID' => env('MAL_CLIENT_ID')
                ])->get('https://api.myanimelist.net/v2/anime/' . $malId . '?fields=id,title,synopsis,genres');

                if ($response && $response->successful()) {
                    $data = $response->json();
                    $description = $data['synopsis'] ?? null;
                    $genres = array_map(function ($genre) {
                        return str_replace('"', "", $genre['name']);
                    }, $data['genres'] ?? []);
                    $genres = $genres ? implode(',', $genres) : null;
                    $logger && $logger("Update description and genres for anime: " . $anime->title . " from MAL");
                }
            }

            // Then try notify.moe if MAL fails
            if (!$description && $notifyMoeId) {
                $response = Http::get('https://notify.moe/api/anime/' . $notifyMoeId);
                if ($response && $response->successful()) {
                    $data = $response->json();
                    $description = $data['summary'] ?? null;
                    $genres = $data['genres'] ? implode(',', $data['genres']) : null;
                    $logger && $logger("Update description and genres for anime: " . $anime->title . " from notify.moe");
                }
            }

            // Finally, try kitsu.io if both MAL and notify.moe fail
            if (!$description && $kitsuId) {
                $response = Http::get('https://kitsu.io/api/edge/anime/' . $kitsuId);
                if ($response && $response->successful()) {
                    $data = $response->json();
                    $description = $data['data']['attributes']['synopsis'] ?? null;

                    $genresResponse = Http::get('https://kitsu.io/api/edge/anime/' . $kitsuId . '/genres');
                    $genresData = $genresResponse->json();
                    $genres = array_map(function ($genre) {
                        return $genre['attributes']['name'];
                    }, $genresData['data'] ?? []);
                    $genres = $genres ? implode(',', $genres) : null;
                    $logger && $logger("Update description and genres for anime: " . $anime->title . " from kitsu.io");
                }
            }

            if ($description) {
                $this->updateAnimeData($anime, $description, $genres, $sqlFile, $logger);
                $count++;
            } else {
                $logger && $logger("Failed to update description and genres for anime: " . $anime->title);
                Log::error('Failed to fetch additional data for anime: ' . $anime->title);
            }
            sleep(15);
        }

        if ($generateSqlFile) {
            fclose($sqlFile);
        }

        $duration = microtime(true) - $startTime;
        return [
            'count' => $count,
            'total' => $total,
            'duration' => $duration,
        ];
    }

    private function updateAnimeData($anime, $description, $genres, $sqlFile, $logger)
    {
        DB::table('anime')
            ->where('id', $anime->id)
            ->update([
                'description' => $description,
                'genres' => $genres
            ]);

        if ($sqlFile) {
            $escapedDescription = addslashes($description);
            $escapedGenres = addslashes($genres);
            $updateQuery = "UPDATE anime SET description = '$escapedDescription', genres = '$escapedGenres' WHERE title = '$anime->title' AND anime_type_id = $anime->anime_type_id AND anime_status_id = $anime->anime_status_id AND season = '$anime->season' AND year = $anime->year AND episodes = $anime->episodes;\n";
            fwrite($sqlFile, $updateQuery);
        }
    }
}
