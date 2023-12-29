<?php
namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeStatus;
use App\Models\AnimeType;
use Illuminate\Support\Facades\Log;

class AnimeImportService
{
    public function importFromJsonFile($filePath, $fullUpdate, $logger = null)
    {
        $count = 0;
        $json = file_get_contents($filePath);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $total = count($data['data']);

        $startTime = microtime(true);
        $dbIdCounter = 1;
        foreach ($data['data'] as $animeData) {
            $title = str_replace('"', '', $animeData['title']);
            //It's unlikely that full updates will ever be as good as clean database setup since anime can be deleted which we don't really handle and possibly also updated in ways we might not expect, but it's better than not having it, especially with all of our logging for full updates. Full updates are important since re-creating the entire database isn't feasible because the anime data is very relational and used in reviews, user anime lists, etc.
            if ($fullUpdate) {
                //We can use dbIdCounter for testing purposes.
                //$dbIdCounter++;
                //if ($dbIdCounter > 100) exit;
                $animeWithSameTitle = Anime::where('title', $title)->get();
                if ($animeWithSameTitle->count() === 1) {
                    $existingAnime = $animeWithSameTitle->first();
                    $originalData = $existingAnime->toArray();

                    // Fetch or create type and status
                    $type = AnimeType::where('type', $animeData['type'] ?? 'UNKNOWN')->firstOrCreate(['type' => $animeData['type'] ?? 'UNKNOWN']);
                    $status = AnimeStatus::where('status', $animeData['status'] ?? 'UNKNOWN')->firstOrCreate(['status' => $animeData['status'] ?? 'UNKNOWN']);

                    // Prepare data for comparison and update
                    $updateData = [];
                    if ($existingAnime->anime_type_id !== $type->id) $updateData['anime_type_id'] = $type->id;
                    if ($existingAnime->anime_status_id !== $status->id) $updateData['anime_status_id'] = $status->id;
                    if ($existingAnime->episodes !== $animeData['episodes']) $updateData['episodes'] = $animeData['episodes'];
                    if ($existingAnime->season !== $animeData['animeSeason']['season']) $updateData['season'] = $animeData['animeSeason']['season'];
                    if ($existingAnime->year !== $animeData['animeSeason']['year']) $updateData['year'] = $animeData['animeSeason']['year'];
                    if ($existingAnime->picture !== $animeData['picture']) $updateData['picture'] = $animeData['picture'];
                    if ($existingAnime->thumbnail !== $animeData['thumbnail']) $updateData['thumbnail'] = $animeData['thumbnail'];
                    if ($existingAnime->synonyms !== implode(', ', $animeData['synonyms'])) $updateData['synonyms'] = implode(', ', $animeData['synonyms']);
                    if ($existingAnime->relations !== implode(', ', $animeData['relations'])) $updateData['relations'] = implode(', ', $animeData['relations']);
                    if ($existingAnime->sources !== implode(', ', $animeData['sources'])) $updateData['sources'] = implode(', ', $animeData['sources']);
                    if ($existingAnime->tags !== implode(', ', $animeData['tags'])) $updateData['tags'] = implode(', ', $animeData['tags']);

                    // Perform update if there are changes
                    if (!empty($updateData)) {
                        // It was updated, so let's force a re-download of the descriptions etc just in case.
                        // We're not even emptying out descriptions so if it already has a description when downloading descriptions, it will keep this false because it's not true (the description technically isn't empty), otherwise it will set it to true again (if it fails to download the description again).
                        // Either way, both scenarios are fine and shouldn't cause problems.
                        $updateData['api_descriptions_empty'] = false;

                        $existingAnime->update($updateData);
                        $updatedData = $existingAnime->refresh()->toArray(); // Refresh and get updated data

                        // Log original and updated details
                        $logger && $logger("Updated details for anime ID {$existingAnime->id} with title $title, updated data only: " . print_r($updateData, true));
                        Log::channel('anime_import')->info("Updated details for anime ID {$existingAnime->id} with title $title, updated data only: " . print_r($updateData, true));
                        Log::channel('anime_import')->info("Anime ID {$existingAnime->id} with title: $title Updated, Original anime data: " . json_encode($originalData));
                        Log::channel('anime_import')->info("Anime ID {$existingAnime->id} with title: $title Updated, Updated anime data: " . json_encode($updatedData));
                    } else {
                        $logger && $logger("No updates required for anime ID {$existingAnime->id} with title: $title");
                    }
                    continue;
                }
                if ($animeWithSameTitle->count() > 1) {
                    // Handle duplicates
                    $logger && $logger("Duplicate anime found with title $title");
                    Log::channel('anime_import')->info("Duplicate anime found with title $title");
                    foreach ($animeWithSameTitle as $duplicate) {
                        $logger && $logger("Duplicate ID: {$duplicate->id}, Title: {$duplicate->title}, Season: {$duplicate->season}, Year: {$duplicate->year}, Type: {$duplicate->anime_type->type}, Status: {$duplicate->anime_status->status}");
                        Log::channel('anime_import')->info("Duplicate ID: {$duplicate->id}, Title: {$duplicate->title}, Season: {$duplicate->season}, Year: {$duplicate->year}, Type: {$duplicate->anime_type->type}, Status: {$duplicate->anime_status->status}");
                    }
                    continue;
                }
                $logger && $logger("No existing anime found for title $title, continuing to add...");
            }

            //We have to be very careful with incremental imports. It's very easy to not be able to uniquely identify anime,
            //since anime could have an unknown season in the current year, then get updated with a season,
            //then it won't match. That's one example. There's so many other examples.
            $existingAnime = Anime::where('title', $title)
                ->where('year', $animeData['animeSeason']['year'])
                ->whereHas('anime_type', function ($query) use ($animeData) {
                    $query->where('type', $animeData['type']);
                })
                ->where('episodes', $animeData['episodes'])
                ->where('season', $animeData['animeSeason']['season'])
                //->where('thumbnail', $animeData['thumbnail'])
                //->where('sources', implode(', ', $animeData['sources']))
                ->first();

            if ($existingAnime) {
                $logger && $logger("Skipping existing anime: " . $title);
                continue;
            }

            $type = AnimeType::where('type', $animeData['type'] ?? 'UNKNOWN')->firstOrCreate(['type' => $animeData['type'] ?? 'UNKNOWN']);
            if ($type->wasRecentlyCreated) {
                $logger && $logger("New anime type created: " . $type->type);
            }
            $status = AnimeStatus::where('status', $animeData['status'] ?? 'UNKNOWN')->firstOrCreate(['status' => $animeData['status'] ?? 'UNKNOWN']);
            if ($status->wasRecentlyCreated) {
                $logger && $logger("New anime status created: " . $status->status);
            }

            Anime::create([
                'title' => $title,
                'anime_type_id' => $type->id,
                'episodes' => $animeData['episodes'],
                'anime_status_id' => $status->id,
                'season' => $animeData['animeSeason']['season'],
                'year' => $animeData['animeSeason']['year'],
                'picture' => $animeData['picture'],
                'thumbnail' => $animeData['thumbnail'],
                'synonyms' => implode(', ', $animeData['synonyms']),
                'relations' => implode(', ', $animeData['relations']),
                'sources' => implode(', ', $animeData['sources']),
                'tags' => implode(', ', $animeData['tags']),
            ]);
            $episodes = $animeData['episodes'];
            $type = $type->type;
            $status = $status->status;
            $season = $animeData['animeSeason']['season'];
            $year = $animeData['animeSeason']['year'];
            //Technically, we could check if there's any anime with a title match, although there would likely be many false positive matches if we're not careful.
            $logger && $logger("New anime created: $title, Episodes: $episodes, Type: $type, Status ID: $status, Season: $season, Year: $year");
            //Let's also log this to a file since it's important.
            Log::channel('anime_import')->info("New anime imported: $title, Episodes: $episodes, Type: $type, Status: $status, Season: $season, Year: $year");
            $count++;
        }

        $duration = microtime(true) - $startTime;
        return [
            'count' => $count,
            'total' => $total,
            'duration' => $duration,
        ];
    }
}
