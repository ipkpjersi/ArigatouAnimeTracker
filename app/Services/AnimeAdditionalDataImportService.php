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
        // Fetch anime records without description and genres
        $animes = DB::table('anime')
                    ->whereNull('description')
                    ->orWhereNull('genres')
                    ->get();
        $total = count($animes);

        // Open file for appending SQL statements if needed
        $sqlFile = $generateSqlFile ? fopen(('database/seeders/anime_additional_data.sql'), 'a') : null;

        // Loop through each anime
        foreach ($animes as $anime) {

            // Make the API call
            $response = Http::withHeaders([
                'X-MAL-CLIENT-ID' => env('MAL_CLIENT_ID')
            ])->get('https://api.myanimelist.net/v2/anime/' . $anime->id . '?fields=id,title,synopsis,genres');

            // Check if it returns a response
            if ($response->successful()) {
                $data = $response->json();

                // Extract and prepare the data
                $description = $data['synopsis'] ?? null;

                // Optionally, remove extra text from the description and remove the trailing lines.
                //$description = trim(str_replace("[Written by MAL Rewrite]", "(Source: Funimation)", ''));

                $genres = array_map(function ($genre) {
                    return $genre['name'];
                }, $data['genres'] ?? []);

                // Convert the array to a comma-separated string.
                $genres = implode(',', $genres);

                // Update the anime table
                DB::table('anime')
                    ->where('id', $anime->id)
                    ->update([
                        'description' => $description,
                        'genres' => $genres
                    ]);

                if ($generateSqlFile && $sqlFile) {
                    $escapedDescription = addslashes($description);  // Make sure to escape any special characters
                    $escapedGenres = addslashes($genres);
                    $updateQuery = "UPDATE anime SET description = '$escapedDescription', genres = '$escapedGenres' WHERE title = '$anime->title' AND anime_type_id = $anime->anime_type_id AND anime_status_id = $anime->anime_status_id AND season = '$anime->season' AND year = $anime->year AND episodes = $anime->episodes;\n";
                    fwrite($sqlFile, $updateQuery);
                }
                $logger && $logger("Updated description and genres for anime: " . $anime->title);
                $count++;
            } else {
                $logger && $logger("Failed to update description and genres for anime: " . $anime->title);
                \Log::error('Failed to fetch data for anime: ' . $anime->title . ': ' . $response->body());
                //Optionally, we could try an additional API to fetch more descriptions and genres.
            }

            // Pause for 15 seconds to avoid rate-limiting
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
}
