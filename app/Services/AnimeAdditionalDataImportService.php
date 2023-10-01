<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnimeAdditionalDataImportService
{
    public function downloadAdditionalAnimeData($logger = null, $generateSqlFile = false, $apiDescriptionsEmptyOnly = false)
    {
        $startTime = microtime(true);
        $count = 0;
        $all = DB::table('anime')
                    ->get();
        $anime = DB::table('anime')
                    ->where("api_descriptions_empty", "=", $apiDescriptionsEmptyOnly ? "true" : "false")
                    ->whereNull('description')
                    ->whereNull('genres')
                    ->get();
        $total = $all->count();
        $downloading = $anime->count();
        $logger && $logger("Downloading additional anime data for $downloading out of $total anime.");
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
            $malRank = null;
            $malMean = null;
            $malPopularity = null;
            $malUsers = null;
            $malMembers = null;

            // Try MAL first
            if ($malId) {
                $response = Http::withHeaders([
                    'X-MAL-CLIENT-ID' => config('global.mal_client_id')
                ])->get('https://api.myanimelist.net/v2/anime/' . $malId . '?fields=id,title,synopsis,genres,mean,rank,popularity,num_scoring_users,num_list_users');

                if ($response && $response->successful()) {
                    $data = $response->json();
                    $description = $data['synopsis'] ?? null;
                    $genres = array_map(function ($genre) {
                        return str_replace('"', "", $genre['name']);
                    }, $data['genres'] ?? []);
                    $genres = $genres ? implode(',', $genres) : null;
                    $malRank = $data['rank'] ?? null;
                    $malMean = $data['mean'] ?? null;
                    $malPopularity = $data['popularity'] ?? null;
                    $malUsers = $data['num_scoring_users'] ?? null; //The users who have scored/ranked the anime
                    $malMembers = $data['num_list_users'] ?? null; //The members with this anime on their list.

                    $logger && $logger("Update data for anime: " . $row->title . " from MAL");
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
                $this->updateAnimeData($row, $description, $genres, $malRank, $malMean, $malPopularity, $malUsers, $malMembers, $sqlFile, $logger);
                $logger && $logger("Successfully updated description and genres for anime: " . $row->title);
                $count++;
            } else {
                $logger && $logger("Failed to update description and genres for anime: " . $row->title);
                Log::error('Failed to fetch additional data for anime: ' . $row->title);
                DB::table('anime')
                ->where('id', $row->id)
                ->update(['api_descriptions_empty' => true]);
            }
            $sleepTime = config("global.additional_data_service_sleep_time", 15);
            $logger && $logger("Sleeping for $sleepTime seconds");
            sleep($sleepTime);
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
        $hasError = false;
        $sqlPath = database_path('seeders/anime_additional_data.sql');
        $anime = DB::table('anime')->get();
        $total = count($anime);
        if (File::exists($sqlPath)) {
            $sqlContent = File::get($sqlPath);
            $sqlQueries = explode(";\n", $sqlContent);
            foreach ($sqlQueries as $query) {
                if (trim($query) !== '') {
                    try {
                        DB::unprepared($query . ';');
                        $logger && $logger("Importing additional anime data for anime " . ($count + 1));
                        $count++;
                    } catch (\Exception $e) {
                        $hasError = true;
                        $logger && $logger("Error importing additional anime data: " . $e->getMessage() . "\n Error on query: " . $query);
                        $logger && $logger("Imported {$count} SQL queries out of {$total} before running into an error.");
                        break;
                    }
                }
            }
            if (!$hasError) {
                $logger && $logger("Imported {$count} SQL queries successfully.");
            }
        } else {
            $logger && $logger('Error importing additional anime data: SQL file does not exist.');
        }
        $duration = microtime(true) - $startTime;
        return [
            'count' => $count,
            'total' => $total,
            'duration' => $duration,
        ];
    }

    private function updateAnimeData($anime, $description, $genres, $malRank, $malMean, $malPopularity, $malScoringUsers, $malListMembers, $sqlFile, $logger = null)
    {
        $updateData = [];

        if ($description !== null) $updateData['description'] = $description;
        if ($genres !== null) $updateData['genres'] = $genres;
        if ($malRank !== null) $updateData['mal_rank'] = $malRank;
        if ($malMean !== null) $updateData['mal_mean'] = $malMean;
        if ($malPopularity !== null) $updateData['mal_popularity'] = $malPopularity;
        if ($malScoringUsers !== null) $updateData['mal_scoring_users'] = $malScoringUsers;
        if ($malListMembers !== null) $updateData['mal_list_members'] = $malListMembers;

        if (!empty($updateData)) {
            DB::table('anime')
                ->where('id', $anime->id)
                ->update($updateData);
        }

        if ($sqlFile) {
            $year = empty($anime->year) ? 'NULL' : $anime->year;
            $season = empty($anime->season) ? 'NULL' : "'$anime->season'";
            $title = addslashes(str_replace('"', '', $anime->title));
            $malMean = !empty($malMean) ? $malMean : $anime->mal_mean ?? 'NULL';
            $malRank = !empty($malRank) ? $malRank : $anime->mal_rank ?? 'NULL';
            $malPopularity = !empty($malPopularity) ? $malPopularity : $anime->mal_popularity ?? 'NULL';
            $malScoringUsers = !empty($malScoringUsers) ? $malScoringUsers : $anime->mal_scoring_users ?? 'NULL';
            $malListMembers = !empty($malListMembers) ? $malListMembers : $anime->mal_list_members ?? 'NULL';
            $description = !empty($description) ? addslashes($description) : $anime->description ?? 'NULL';
            $genres = !empty($genres) ? addslashes($genres) : $anime->genres ?? 'NULL';
            $updateQuery = "UPDATE anime SET description = '$description', genres = '$genres', mal_mean = $malMean, mal_rank = $malRank, mal_popularity = $malPopularity, mal_scoring_users = $malScoringUsers, mal_list_members = $malListMembers WHERE title = '$title' AND anime_type_id = $anime->anime_type_id AND anime_status_id = $anime->anime_status_id AND season = $season AND year = $year AND episodes = $anime->episodes;\n";
            fwrite($sqlFile, $updateQuery);
        }
    }


}
