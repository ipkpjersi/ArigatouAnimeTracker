<?php
namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeUser;
use App\Models\AnimeType;
use App\Models\AnimeStatus;
use App\Models\WatchStatus;
use Illuminate\Support\Facades\Auth;
use SimpleXMLElement;

class MyAnimeListImportService
{
    public function import(string $xmlContent, $userId, $logger = null)
    {
        try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (\Exception $e) {
            \Log::error("An exception has occurred importing a MyAnimeList export: "  . $e->getMessage());
        }

        $count = 0;
        $startTime = microtime(true);
        $total = count($xml->anime);
        foreach ($xml->anime as $animeData) {
            $title = str_replace('"', '', (string)$animeData->series_title);
            $type = (string)$animeData->series_type;
            $episodes = (int)$animeData->series_episodes;
            $watchStatus = strtoupper(str_replace(" ", "-", (string)$animeData->my_status));
            $score = (int)$animeData->my_score;
            $progress = (int)$animeData->my_watched_episodes;
            $animeType = AnimeType::firstOrCreate(['type' => $type]);
            // Check for existing anime
            $existingAnime = Anime::where('title', $title)
                ->where('anime_type_id', $animeType->id)
                ->where('episodes', $episodes)
                ->first();

            $animeId = $existingAnime->id ?? null;

            if (!$animeId) {
                \Log::info("Could not find match for anime $title with type {$animeType->type} and $episodes episodes");
                $logger && $logger("Could not find match for anime $title with type {$animeType->type} and $episodes episodes");
                continue;
            }

            // Check for duplicate anime_user entry
            $existingEntry = AnimeUser::where('anime_id', $animeId)
                ->where('user_id', $userId)
                ->first();

            if ($existingEntry) {
                $logger && $logger("Skipping existing anime $title with type {$animeType->type} and $episodes episodes");
                continue;
            }

            // Handle watch status
            $watchStatusModel = WatchStatus::firstOrCreate(['status' => $watchStatus]);

            // Insert into the anime_user table
            AnimeUser::create([
                'anime_id' => $animeId,
                'user_id' => $userId,
                'watch_status_id' => $watchStatusModel->id,
                'score' => $score,
                'progress' => $progress,
            ]);

            $count++;
        }

        $duration = microtime(true) - $startTime;
        return ["count" => $count, "duration" => $duration, "total" => $total];
    }
}
