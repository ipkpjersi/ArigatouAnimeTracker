<?php
namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeUser;
use App\Models\AnimeType;
use App\Models\AnimeStatus;
use App\Models\WatchStatus;
use Illuminate\Support\Facades\Auth;
use SimpleXMLElement;

class AnimeListImportService
{
    public function import(string $fileContent, $fileType, $userId, $logger = null)
    {
        if ($fileType === 'myanimelist') {
            return $this->importFromMyAnimeList($fileContent, $userId, $logger);
        } elseif ($fileType === 'arigatou') {
            return $this->importFromArigatou($fileContent, $userId, $logger);
        } else {
            //Unknown file type
        }
    }
    private function importFromMyAnimeList(string $xmlContent, $userId, $logger = null)
    {
        $count = 0;
        $startTime = microtime(true);
         try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (\Exception $e) {
            \Log::error("An exception has occurred importing a MyAnimeList export: "  . $e->getMessage());
        }
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
        return ["count" => $count, "total" => $total, "duration" => $duration];
    }

    private function importFromArigatou(string $jsonContent, $userId, $logger = null)
    {
        // New JSON parsing logic here
    }
}
