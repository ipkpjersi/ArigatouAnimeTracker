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

            $type = AnimeType::where('type', $animeData['type'])->firstOrCreate(['type' => $animeData['type']]);
            if ($type->wasRecentlyCreated) {
                $logger && $logger("New anime type created: " . $type->type);
            }
            $status = AnimeStatus::where('status', $animeData['status'])->firstOrCreate(['status' => $animeData['status']]);
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
