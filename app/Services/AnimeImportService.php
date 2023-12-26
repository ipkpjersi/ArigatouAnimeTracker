<?php
namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeStatus;
use App\Models\AnimeType;
use Illuminate\Support\Facades\Log;

class AnimeImportService
{
    public function importFromJsonFile($filePath, $logger = null)
    {
        $count = 0;
        $json = file_get_contents($filePath);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $total = count($data['data']);

        $startTime = microtime(true);

        foreach ($data['data'] as $animeData) {
            $title = str_replace('"', '', $animeData['title']);
            $existingAnime = Anime::where('title', $title)
                ->where('year', $animeData['animeSeason']['year'])
                ->whereHas('anime_type', function ($query) use ($animeData) {
                    $query->where('type', $animeData['type']);
                })
                ->where('episodes', $animeData['episodes'])
                ->where('season', $animeData['animeSeason']['season'])
                ->where('thumbnail', $animeData['thumbnail'])
                ->where('sources', implode(', ', $animeData['sources']))
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
