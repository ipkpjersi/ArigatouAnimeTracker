<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnimeAdditionalDataImportService
{
    public function downloadAdditionalAnimeData($logger = null, $generateSqlFile = false)
    {
        $startTime = microtime(true);
        $count = 0;
        $anime = DB::table('anime')
                    ->whereNull('description')
                    ->whereNull('genres')
                    ->get();
        $total = count($anime);

        $sqlFile = $generateSqlFile ? fopen(('database/seeders/anime_additional_data.sql'), 'a') : null;

        foreach ($anime as $row) {
            $malId = null;
            $notifyMoeId = null;
            $kitsuId = null;
            if (isset($row->sources)) {
                $sources = explode(',', $row->sources);
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
                    $logger && $logger("Update description and genres for anime: " . $row->title . " from MAL");
                }
            }

            // Then try notify.moe if MAL fails
            if ((!$description || !$genres) && $notifyMoeId) {
                $response = Http::get('https://notify.moe/api/anime/' . $notifyMoeId);
                if ($response && $response->successful()) {
                    $data = $response->json();
                    $description = $data['summary'] ?? null;
                    $genres = $data['genres'] ? implode(',', $data['genres']) : null;
                    $logger && $logger("Update description and genres for anime: " . $row->title . " from notify.moe");
                }
            }

            // Finally, try kitsu.io if both MAL and notify.moe fail
            if ((!$description || !$genres) && $kitsuId) {
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
                    $logger && $logger("Update description and genres for anime: " . $row->title . " from kitsu.io");
                }
            }

            if ($description) {
                $this->updateAnimeData($row, $description, $genres, $sqlFile, $logger);
                $count++;
            } else {
                $logger && $logger("Failed to update description and genres for anime: " . $row->title);
                Log::error('Failed to fetch additional data for anime: ' . $row->title);
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

    public function importAdditionalAnimeData($logger = null) {
        $startTime = microtime(true);
        $count = 0;
        $sqlPath = database_path('seeders/anime_additional_data.sql');
        $anime = DB::table('anime')->get();
        $total = count($anime);
        if (File::exists($sqlPath)) {
            $sqlContent = File::get($sqlPath);
            $sqlQueries = explode(";\n", $sqlContent);
            foreach ($sqlQueries as $query) {
                if (trim($query) !== '') {
                    DB::unprepared($query . ';');
                    $count++;
                }
            }
            $logger && $logger("Imported {$count} SQL queries successfully.");
        } else {
            $logger && $logger('SQL file does not exist.');
        }
        $duration = microtime(true) - $startTime;
        return [
            'count' => $count,
            'total' => $total,
            'duration' => $duration,
        ];
    }

    private function updateAnimeData($anime, $description, $genres, $sqlFile, $logger = null)
    {
        DB::table('anime')
            ->where('id', $anime->id)
            ->update([
                'description' => $description,
                'genres' => $genres
            ]);

        if ($sqlFile) {
            $description = addslashes($description);
            $genres = addslashes($genres);
            $year = empty($anime->year) ? 'NULL' : $anime->year;
            $season = empty($anime->season) ? 'NULL' : "'$anime->season'";
            $title = addslashes(str_replace('"', '', $anime->title));
            $updateQuery = "UPDATE anime SET description = '$description', genres = '$genres' WHERE title = '$title' AND anime_type_id = $anime->anime_type_id AND anime_status_id = $anime->anime_status_id AND season = $season AND year = $year AND episodes = $anime->episodes;\n";
            fwrite($sqlFile, $updateQuery);
        }
    }


}
