<?php
namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeStatus;
use App\Models\AnimeType;
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
            if (isset($anime->sources)) {
                $sources = explode(',', $anime->sources);
                foreach ($sources as $source) {
                    if (strpos($source, 'myanimelist.net/anime/') !== false) {
                        $malId = explode('/', rtrim($source, '/'))[4];
                        break;
                    }
                }
            }

            $response = null;
            if ($malId) {
                $response = Http::withHeaders([
                    'X-MAL-CLIENT-ID' => env('MAL_CLIENT_ID')
                ])->get('https://api.myanimelist.net/v2/anime/' . $malId . '?fields=id,title,synopsis,genres');
            }

            if ($response && $response->successful()) {
                $data = $response->json();
                $description = $data['synopsis'] ?? null;
                $genres = array_map(function ($genre) {
                    return $genre['name'];
                }, $data['genres'] ?? []);
                $themes = array_map(function ($theme) {
                    return $theme['name'];
                }, $data['themes'] ?? []);
                $themes = implode(',', $themes);
                $this->updateAnimeData($anime, $description, $genres, $themes, $sqlFile, $logger);
                $count++;
            } else {
                //TODO: implement alternate API, likely kitsu
                //$alternateResponse = Http::get('ALTERNATE_API_URL_HERE');
                $alternateResponse = false;
                if ($alternateResponse && $alternateResponse->successful()) {
                    $this->updateAnimeData($anime, "", "", "", $sqlFile, $logger);
                    $count++;
                } else {
                    $logger && $logger("Failed to update description and genres for anime: " . $anime->title);
                    Log::error('Failed to fetch data for anime: ' . $anime->title);
                }
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

    private function updateAnimeData($anime, $description, $genres, $themes, $sqlFile, $logger)
    {

        DB::table('anime')
            ->where('id', $anime->id)
            ->update([
                'description' => $description,
                'genres' => $genres,
                'themes' => $themes
            ]);

        if ($sqlFile) {
            $escapedDescription = addslashes($description);
            $escapedGenres = addslashes($genres);
            $escapedThemes = addslashes($themes);
            $updateQuery = "UPDATE anime SET description = '$escapedDescription', genres = '$escapedGenres', themes = '$escapedThemes' WHERE title = '$anime->title' AND anime_type_id = $anime->anime_type_id AND anime_status_id = $anime->anime_status_id AND season = '$anime->season' AND year = $anime->year AND episodes = $anime->episodes;\n";
            fwrite($sqlFile, $updateQuery);
        }

        $logger && $logger("Updated description and genres for anime: " . $anime->title);
    }
}
