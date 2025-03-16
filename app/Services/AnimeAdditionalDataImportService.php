<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

use function App\Helpers\safe_json_encode;

class AnimeAdditionalDataImportService
{
    public function downloadAdditionalAnimeData($logger = null, $generateSqlFile = false, $apiDescriptionsEmptyOnly = false)
    {
        $startTime = microtime(true);
        $count = 0;
        $all = DB::table('anime')
            ->get();
        $anime = DB::table('anime')
            ->where('api_descriptions_empty', '=', $apiDescriptionsEmptyOnly ? 'true' : 'false')
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere(DB::raw('TRIM(description)'), '=', '');
            })
            ->where(function ($query) {
                $query->whereNull('genres')
                    ->orWhere(DB::raw('TRIM(genres)'), '=', '');
            })
            ->get();
        $total = $all->count();
        $downloading = $anime->count();
        $logger && $logger("Downloading additional anime data for $downloading out of $total anime.");
        $sqlFile = $generateSqlFile ? fopen(('database/seeders/anime_additional_data.sql'), 'a') : null;
        $sqlFilePath = 'database/seeders/anime_additional_data.sql';
        $zipFilePath = 'database/seeders/anime_additional_data.sql.zip';

        if ($generateSqlFile && ! file_exists($sqlFilePath) && file_exists($zipFilePath)) {
            $logger && $logger('File anime_additional_data.sql does not exist, extracting anime_additional_data.sql.zip to append more data...');
            $this->unzipSqlFile();
        }

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
            } else {
                // This probably shouldn't ever happen, sources should probably always be set, or maybe not.
                $logger && $logger('Sources not set for anime: '.$row->title.' row: '.print_r($row, true));
            }

            $description = null;
            $genres = null;
            $malRank = null;
            $malMean = null;
            $malPopularity = null;
            $malUsers = null;
            $malMembers = null;
            $averageDuration = null;
            $rating = null;
            $source = null;
            $background = null;
            $recommendations = null;
            $studios = null;
            $broadcast = null;
            $relatedAnime = null;
            $relatedManga = null;

            // Try MAL first
            if ($malId) {
                try {
                    // Sometimes the data for certain columns returned by the MAL API is unexpected/unclean even with safe_json_encode, so we could always SELECT DISTINCT columns if necessary and then even hardcode any arrays with said data for any input/display validation. It's better to have the format in an incorrect/weird format than to not have it at all.
                    $response = Http::withHeaders([
                        'X-MAL-CLIENT-ID' => config('global.mal_client_id'),
                    ])->get('https://api.myanimelist.net/v2/anime/'.$malId.'?fields=id,title,synopsis,average_episode_duration,rating,genres,mean,rank,popularity,num_scoring_users,num_list_users,source,background,recommendations,studios,broadcast,related_anime,related_manga');
                    if ($response && $response->successful()) {
                        $data = $response->json();
                        $description = $data['synopsis'] ?? null;
                        $genres = array_map(function ($genre) {
                            return str_replace('"', '', $genre['name']);
                        }, $data['genres'] ?? []);
                        $genres = $genres ? implode(',', $genres) : null;
                        $malRank = $data['rank'] ?? null;
                        $malMean = $data['mean'] ?? null;
                        $malPopularity = $data['popularity'] ?? null;
                        $malUsers = $data['num_scoring_users'] ?? null; // The users who have scored/ranked the anime.
                        $malMembers = $data['num_list_users'] ?? null; // The members with this anime on their list.
                        $averageDuration = $data['average_episode_duration'] ?? null; // The average episode duration (or duration).
                        $rating = $data['rating'] ?? null; // The rating of the series.
                        $source = $data['source'] ?? null; // Is it Manga, LN, etc.
                        $background = $data['background'] ?? null; // A brief description of the background, like it's a 2003 DVD that released in Japan but never released overseas, etc.
                        $recommendations = safe_json_encode($data['recommendations'] ?? []); // Recommended anime by other users.
                        $studios = safe_json_encode($data['studios'] ?? []); // Studio(s) that worked on this anime.
                        $broadcast = safe_json_encode($data['broadcast'] ?? []); // The date and time it was originally broadcast.
                        $relatedAnime = safe_json_encode($data['related_anime'] ?? []); // Any similarly related anime to this.
                        $relatedManga = safe_json_encode($data['related_manga'] ?? []); // Any similarly related manga to this.

                        $logger && $logger('Updated data for anime: '.$row->title.' from MAL');
                    } elseif ($response) {
                        $data = $response->json();
                        $logger && $logger('Failed update response from MAL for anime: '.$row->title.' '.print_r($data, true));
                    }
                } catch (\Exception $e) {
                    $logger && $logger('Error fetching data from MAL for anime: '.$row->title.'. Error: '.$e->getMessage());
                    Log::channel('anime_import')->error('Error fetching data from MAL for anime: '.$row->title.'. Error: '.$e->getMessage());
                }
            } else {
                // Optional logging, we likely don't need this logging unless we know it's not fetching descriptions from MAL when it should be.
                // $logger && $logger("No MAL ID for anime: " . $row->title . ", verify versus DB to see if MAL source exists for this anime");
            }

            // Then try notify.moe if MAL fails
            if ((! $description || ! $genres) && $notifyMoeId) {
                try {
                    $response = Http::get('https://notify.moe/api/anime/'.$notifyMoeId);
                    if ($response && $response->successful()) {
                        $data = $response->json();
                        $description = $data['summary'] ?? null;
                        $genres = $data['genres'] ? implode(',', $data['genres']) : null;
                        $logger && $logger('Updated description and/or genres for anime: '.$row->title.' from notify.moe');
                    }
                } catch (\Exception $e) {
                    $logger && $logger('Error fetching data from notify.moe for anime: '.$row->title.'. Error: '.$e->getMessage());
                    Log::channel('anime_import')->error('Error fetching data from notify.moe for anime: '.$row->title.'. Error: '.$e->getMessage());
                }
            }

            // Finally, try kitsu.io if both MAL and notify.moe fail
            if ((! $description || ! $genres) && $kitsuId) {
                try {
                    $response = Http::get('https://kitsu.io/api/edge/anime/'.$kitsuId);
                    if ($response && $response->successful()) {
                        $data = $response->json();
                        $description = $data['data']['attributes']['synopsis'] ?? null;
                        $genresResponse = Http::get('https://kitsu.io/api/edge/anime/'.$kitsuId.'/genres');
                        $genresData = $genresResponse->json();
                        $genres = array_map(function ($genre) {
                            return $genre['attributes']['name'];
                        }, $genresData['data'] ?? []);
                        $genres = $genres ? implode(',', $genres) : null;
                        $logger && $logger('Updated description and/or genres for anime: '.$row->title.' from kitsu.io');
                    }
                } catch (\Exception $e) {
                    $logger && $logger('Error fetching data from kitsu.io for anime: '.$row->title.'. Error: '.$e->getMessage());
                    Log::channel('anime_import')->error('Error fetching data from kitsu.io for anime: '.$row->title.'. Error: '.$e->getMessage());
                }
            }
            //We only check the description since we don't really need genres to exist in order to update a description, also we can check genres separately and prevent overwriting existing genres with empty ones separately.
            if ($description) {
                //Prevent overwriting existing genres with empty ones, since we only check for a description before updating anime data, we should fetch existing genres if the new genres are empty.
                if (empty($genres)) {
                    $existingGenres = DB::table('anime')
                        ->where('id', $row->id)
                        ->value('genres'); //Fetch only the genres column

                    if (!empty($existingGenres)) {
                        $genres = $existingGenres; //Retain existing genres if they exist
                    }
                }
                $this->updateAnimeData($row, $description, $genres, $malRank, $malMean, $malPopularity, $malUsers, $malMembers, $averageDuration, $rating, $source, $background, $recommendations, $studios, $broadcast, $relatedAnime, $relatedManga, $sqlFile, $logger);
                $logger && $logger('Successfully updated description and genres for anime: '.$row->title);
                Log::channel('anime_import')->info('Successfully updated description and genres for anime: '.$row->title);
                $count++;
            } else {
                $logger && $logger('Failed to fetch/update description and genres for anime: '.$row->title);
                Log::channel('anime_import')->info('Failed to fetch/update description and genres for anime: '.$row->title);
                Log::error('Failed to fetch additional data for anime: '.$row->title);
                DB::table('anime')
                    ->where('id', $row->id)
                    ->update(['api_descriptions_empty' => true]);
            }
            $sleepTime = config('global.additional_data_service_sleep_time', 15);
            $logger && $logger("Sleeping for $sleepTime seconds");
            sleep($sleepTime);
        }

        if ($generateSqlFile) {
            fclose($sqlFile);
            $this->zipSqlFile();
        }

        $duration = microtime(true) - $startTime;

        return [
            'count' => $count,
            'total' => $total,
            'duration' => $duration,
        ];
    }

    public function importAdditionalAnimeData($logger = null)
    {
        $startTime = microtime(true);
        $count = 0;
        $hasError = false;
        $sqlPath = database_path('seeders/anime_additional_data.sql');
        if (! File::exists($sqlPath)) {
            $this->unzipSqlFile();
        }
        $anime = DB::table('anime')->get();
        $total = count($anime);
        if (File::exists($sqlPath)) {
            $sqlContent = File::get($sqlPath);
            $sqlQueries = explode(";\n", $sqlContent);
            $total = count($sqlQueries);
            foreach ($sqlQueries as $query) {
                if (trim($query) !== '') {
                    try {
                        DB::unprepared($query.';');
                        $logger && $logger('Importing additional anime data for anime '.($count + 1));
                        $count++;
                    } catch (\Exception $e) {
                        $hasError = true;
                        $logger && $logger('Error importing additional anime data: '.$e."\n Error on query: ".$query);
                        $logger && $logger("Imported {$count} SQL queries out of {$total} before running into an error.");
                        break;
                    }
                }
            }
            if (! $hasError) {
                $logger && $logger("Imported {$count} out of {$total} additional anime data SQL queries successfully.");
                Log::channel('anime_import')->info("Imported {$count} out of {$total} additional anime data SQL queries successfully.");
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

    private function updateAnimeData($anime, $description, $genres, $malRank, $malMean, $malPopularity, $malScoringUsers, $malListMembers, $averageDuration, $rating, $source, $background, $recommendations, $studios, $broadcast, $relatedAnime, $relatedManga, $sqlFile, $logger = null)
    {
        $updateData = [];

        if ($description !== null) {
            $updateData['description'] = $description;
        }
        if ($genres !== null) {
            $updateData['genres'] = $genres;
        }
        if ($malRank !== null) {
            $updateData['mal_rank'] = $malRank;
        }
        if ($malMean !== null) {
            $updateData['mal_mean'] = $malMean;
        }
        if ($malPopularity !== null) {
            $updateData['mal_popularity'] = $malPopularity;
        }
        if ($malScoringUsers !== null) {
            $updateData['mal_scoring_users'] = $malScoringUsers;
        }
        if ($malListMembers !== null) {
            $updateData['mal_list_members'] = $malListMembers;
        }
        if ($averageDuration !== null) {
            $updateData['duration'] = $averageDuration;
        }
        if ($averageDuration !== null) {
            $updateData['duration_downloaded'] = 1;
        }
        if ($rating !== null) {
            $updateData['rating'] = $rating;
        }
        if ($rating !== null) {
            $updateData['rating_downloaded'] = 1;
        }
        if ($source !== null) {
            $updateData['source'] = $source;
        }
        if ($background !== null) {
            $updateData['background'] = $background;
        }
        if ($recommendations !== null) {
            $updateData['recommendations'] = $recommendations;
        }
        if ($studios !== null) {
            $updateData['studios'] = $studios;
        }
        if ($broadcast !== null) {
            $updateData['broadcast'] = $broadcast;
        }
        if ($relatedAnime !== null) {
            $updateData['related_anime'] = $relatedAnime;
        }
        if ($relatedManga !== null) {
            $updateData['related_manga'] = $relatedAnime;
        }

        if (! empty($updateData)) {
            DB::table('anime')
                ->where('id', $anime->id)
                ->update($updateData);
        }

        if ($sqlFile) {
            $year = empty($anime->year) ? 'NULL' : $anime->year;
            $season = empty($anime->season) ? 'NULL' : "'$anime->season'";
            $title = addslashes(str_replace('"', '', $anime->title));
            $malMean = ! empty($malMean) ? $malMean : $anime->mal_mean ?? 'NULL';
            $malRank = ! empty($malRank) ? $malRank : $anime->mal_rank ?? 'NULL';
            $malPopularity = ! empty($malPopularity) ? $malPopularity : $anime->mal_popularity ?? 'NULL';
            $malScoringUsers = ! empty($malScoringUsers) ? $malScoringUsers : $anime->mal_scoring_users ?? 'NULL';
            $malListMembers = ! empty($malListMembers) ? $malListMembers : $anime->mal_list_members ?? 'NULL';
            $description = ! empty($description) ? addslashes($description) : $anime->description ?? 'NULL';
            $genres = ! empty($genres) ? addslashes($genres) : $anime->genres ?? 'NULL';
            $averageDuration = ! empty($averageDuration) ? $averageDuration : $anime->duration ?? 'NULL';
            $durationDownloaded = ! empty($averageDuration) && $averageDuration !== 'NULL' ? 1 : 0;
            $rating = ! empty($rating) ? addslashes($rating) : $anime->rating ?? 'NULL';
            $ratingDownloaded = ! empty($rating) && $rating !== 'NULL' ? 1 : 0;
            $source = ! empty($source) ? addslashes($source) : $anime->source ?? 'NULL';
            $background = ! empty($background) ? addslashes($background) : $anime->background ?? 'NULL';
            $recommendations = ! empty($recommendations) ? addslashes($recommendations) : $anime->recommendations ?? 'NULL';
            $studios = ! empty($studios) ? addslashes($studios) : $anime->studios ?? 'NULL';
            $broadcast = ! empty($broadcast) ? addslashes($broadcast) : $anime->broadcast ?? 'NULL';
            $relatedAnime = ! empty($relatedAnime) ? addslashes($relatedAnime) : $anime->related_anime ?? 'NULL';
            $relatedManga = ! empty($relatedManga) ? addslashes($relatedManga) : $anime->related_manga ?? 'NULL';

            $updateQuery = "UPDATE anime SET description = '$description', genres = '$genres', mal_mean = $malMean, mal_rank = $malRank, mal_popularity = $malPopularity, mal_scoring_users = $malScoringUsers, mal_list_members = $malListMembers, duration = $averageDuration, duration_downloaded = $durationDownloaded, rating = '$rating', rating_downloaded = $ratingDownloaded, source = '$source', background = '$background', recommendations = '$recommendations', studios = '$studios', broadcast = '$broadcast', related_anime = '$relatedAnime', related_manga = '$relatedManga' WHERE title = '$title' AND anime_type_id = $anime->anime_type_id AND anime_status_id = $anime->anime_status_id AND season = $season AND year = $year AND episodes = $anime->episodes;\n";
            fwrite($sqlFile, $updateQuery);
        }
    }

    private function zipSqlFile()
    {
        $sqlPath = database_path('seeders/anime_additional_data.sql');
        $zipPath = database_path('seeders/anime_additional_data.zip');

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($sqlPath, 'anime_additional_data.sql');
            $zip->close();
        } else {
            throw new \RuntimeException('Failed to create ZIP archive for SQL file.');
        }
    }

    private function unzipSqlFile()
    {
        $zipPath = database_path('seeders/anime_additional_data.zip');
        $sqlPath = database_path('seeders/anime_additional_data.sql');

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo(database_path('seeders/'));
            $zip->close();
        } else {
            throw new \RuntimeException('Failed to unzip SQL file.');
        }
    }
}
