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
    public function downloadAdditionalAnimeData($logger = null, $generateSqlFile = false, $apiEmptyOnly = false, $forceMalDetailsRedownload = false)
    {
        $startTime = microtime(true);
        $count = 0;
        $all = DB::table('anime')
            ->get();
        $anime = DB::table('anime')
            ->where(function ($query) use ($apiEmptyOnly, $forceMalDetailsRedownload) {
                // Original case: the anime is missing both its description and
                // its genres, so we need to fetch those. This path is gated by
                // the api_descriptions_empty retry flag. That column is a
                // tinyint(1), so compare against the integers 1/0 rather than
                // the strings 'true'/'false' (both of which MySQL casts to 0,
                // which made the empty-only retry mode target the wrong rows).
                $query->where(function ($subQuery) use ($apiEmptyOnly) {
                    $subQuery->where('api_descriptions_empty', '=', $apiEmptyOnly ? 1 : 0)
                        ->where(function ($descQuery) {
                            $descQuery->whereNull('description')
                                ->orWhere(DB::raw('TRIM(description)'), '=', '');
                        })
                        ->where(function ($genresQuery) {
                            $genresQuery->whereNull('genres')
                                ->orWhere(DB::raw('TRIM(genres)'), '=', '');
                        });
                })
                    // New case: the anime already has a description/genres (so it
                    // was skipped above) but some of its MAL details are still
                    // missing. This mirrors the description gap-fill above: we key
                    // off the data being empty so missing details keep being
                    // retried, and use the mal_details_empty flag the same way
                    // api_descriptions_empty is used, to demote confirmed-empty
                    // anime to the empty-only retry pass instead of re-attempting
                    // them on every normal pass. We treat MAL details as
                    // incomplete when EITHER studios OR mal_mean is empty, since
                    // both are MAL-provided: MAL publishes studios as soon as an
                    // anime is announced but withholds the score/rank until the
                    // anime crosses a minimum scoring-member threshold, so an
                    // anime imported while airing gets studios immediately and
                    // would otherwise be treated as complete and never re-checked
                    // for the score MAL publishes later. Checking mal_mean too is
                    // what catches that case. When forceMalDetailsRedownload is
                    // set we ignore both and re-fetch every anime with a MAL source.
                    ->orWhere(function ($subQuery) use ($apiEmptyOnly, $forceMalDetailsRedownload) {
                        $subQuery->where('sources', 'LIKE', '%myanimelist.net/anime/%');
                        if (! $forceMalDetailsRedownload) {
                            $subQuery->where('mal_details_empty', '=', $apiEmptyOnly ? 1 : 0)
                                ->where(function ($malEmptyQuery) {
                                    $malEmptyQuery->whereNull('studios')
                                        ->orWhere(DB::raw('TRIM(studios)'), '=', '')
                                        ->orWhere('studios', '=', '[]')
                                        ->orWhereNull('mal_mean')
                                        ->orWhere(DB::raw('TRIM(mal_mean)'), '=', '');
                                });
                        }
                    });
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
                        // Also record MAL successes to the anime_import log, not
                        // just the console. Previously only the error path below
                        // was written to the file, so a run's log could show MAL
                        // failures with zero successes and make it look like MAL
                        // was never reached when it actually worked fine.
                        Log::channel('anime_import')->info('Updated data for anime: '.$row->title.' from MAL. mean: '.($malMean ?? 'null').', rank: '.($malRank ?? 'null').', scoring_users: '.($malUsers ?? 'null'));

                        // Note which MAL stat fields came back empty. MAL does
                        // not publish a mean score or rank (and sometimes not
                        // even scoring users) until an anime crosses a minimum
                        // scoring-member threshold, so these are routinely null
                        // for low-popularity/new anime even when popularity and
                        // members are present. Logging this makes it clear the
                        // gap is MAL withholding the data, not our import.
                        $missingMalFields = array_keys(array_filter([
                            'rank' => $malRank,
                            'mean' => $malMean,
                            'popularity' => $malPopularity,
                            'scoring_users' => $malUsers,
                            'members' => $malMembers,
                        ], function ($value) {
                            return empty($value);
                        }));
                        if ($missingMalFields) {
                            $logger && $logger('MAL returned no '.implode(', ', $missingMalFields).' for anime: '.$row->title);
                            // Log withheld MAL fields to the file too, so a later
                            // missing score can be traced to MAL withholding it
                            // (below its scoring threshold) rather than an import bug.
                            Log::channel('anime_import')->info('MAL returned no '.implode(', ', $missingMalFields).' for anime: '.$row->title);
                        }
                    } elseif ($response) {
                        $data = $response->json();
                        $logger && $logger('Failed update response from MAL for anime: '.$row->title.' '.print_r($data, true));
                        // A non-2xx MAL response (bad/removed MAL id, auth issue,
                        // rate limit) is distinct from a network exception, so
                        // record it to the file as well rather than console-only.
                        Log::channel('anime_import')->warning('Failed update response from MAL for anime: '.$row->title.' '.print_r($data, true));
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
                        $description = $data['data']['attributes']['synopsis'] ?? null; // There seems to be a synopsis variable and a description variable, but their API docs only mention synopsis so let's use synopsis for now.
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
            // We only check the description since we don't really need genres to exist in order to update a description, also we can check genres separately and prevent overwriting existing genres with empty ones separately.
            if ($description) {
                // Prevent overwriting existing genres with empty ones, since we only check for a description before updating anime data, we should fetch existing genres if the new genres are empty.
                if (empty($genres)) {
                    $existingGenres = DB::table('anime')
                        ->where('id', $row->id)
                        ->value('genres'); // Fetch only the genres column

                    if (! empty($existingGenres)) {
                        $genres = $existingGenres; // Retain existing genres if they exist
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
            // Mirror api_descriptions_empty for MAL details: if this anime has a
            // MAL source but the fetch left some MAL detail empty (no studios or
            // no mal_mean score), flag it as empty so it is retried via the
            // empty-only pass rather than on every normal pass. This uses the same
            // studios-OR-mal_mean gap as the selection query above: MAL withholds
            // the score until an anime crosses a minimum scoring-member threshold,
            // so a still-empty score is the signal that there is more to fetch
            // later. Reset the flag with app:clear-anime-mal-details-empty to
            // retry from the normal pass.
            if ($malId && (empty($studios) || $studios === '[]' || empty($malMean))) {
                DB::table('anime')
                    ->where('id', $row->id)
                    ->update(['mal_details_empty' => true]);
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

        // Use !empty() rather than !== null throughout so an empty or zero value
        // from a sparse API response never overwrites existing known-good data.
        // This also keeps this path consistent with the SQL-file path below.
        if (! empty($description)) {
            $updateData['description'] = $description;
        }
        if (! empty($genres)) {
            $updateData['genres'] = $genres;
        }
        // A real MAL rank/score/popularity/user count is never 0.
        if (! empty($malRank)) {
            $updateData['mal_rank'] = $malRank;
        }
        if (! empty($malMean)) {
            $updateData['mal_mean'] = $malMean;
        }
        if (! empty($malPopularity)) {
            $updateData['mal_popularity'] = $malPopularity;
        }
        if (! empty($malScoringUsers)) {
            $updateData['mal_scoring_users'] = $malScoringUsers;
        }
        if (! empty($malListMembers)) {
            $updateData['mal_list_members'] = $malListMembers;
        }
        if (! empty($averageDuration)) {
            $updateData['duration'] = $averageDuration;
            $updateData['duration_downloaded'] = 1;
        }
        if (! empty($rating)) {
            $updateData['rating'] = $rating;
            $updateData['rating_downloaded'] = 1;
        }
        if (! empty($source)) {
            $updateData['source'] = $source;
        }
        if (! empty($background)) {
            $updateData['background'] = $background;
        }
        // These are JSON-encoded arrays, so an empty result is the literal "[]"
        // (not null or ''). Skip it so we never wipe existing data with "[]".
        if (! empty($recommendations) && $recommendations !== '[]') {
            $updateData['recommendations'] = $recommendations;
        }
        if (! empty($studios) && $studios !== '[]') {
            $updateData['studios'] = $studios;
        }
        if (! empty($broadcast) && $broadcast !== '[]') {
            $updateData['broadcast'] = $broadcast;
        }
        if (! empty($relatedAnime) && $relatedAnime !== '[]') {
            $updateData['related_anime'] = $relatedAnime;
        }
        if (! empty($relatedManga) && $relatedManga !== '[]') {
            $updateData['related_manga'] = $relatedManga;
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
            // Treat an empty JSON array ("[]") as empty so we fall back to the
            // existing value instead of overwriting it with "[]".
            $recommendations = (! empty($recommendations) && $recommendations !== '[]') ? addslashes($recommendations) : $anime->recommendations ?? 'NULL';
            $studios = (! empty($studios) && $studios !== '[]') ? addslashes($studios) : $anime->studios ?? 'NULL';
            $broadcast = (! empty($broadcast) && $broadcast !== '[]') ? addslashes($broadcast) : $anime->broadcast ?? 'NULL';
            $relatedAnime = (! empty($relatedAnime) && $relatedAnime !== '[]') ? addslashes($relatedAnime) : $anime->related_anime ?? 'NULL';
            $relatedManga = (! empty($relatedManga) && $relatedManga !== '[]') ? addslashes($relatedManga) : $anime->related_manga ?? 'NULL';

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
