<?php

namespace App\Console\Commands;

use App\Services\DuplicateAnimeService;
use Illuminate\Console\Command;

class MergeAnimeDuplicate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-anime-duplicate {oldAnimeId} {newAnimeId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge duplicate anime by replacing old anime ID with a new one and deleting the old entry.';

    /**
     * Execute the console command.
     */
    public function handle(DuplicateAnimeService $duplicateAnimeService): void
    {
        $oldAnimeId = $this->argument('oldAnimeId');
        $newAnimeId = $this->argument('newAnimeId');

        $this->info("Fetching details for anime ID $oldAnimeId and $newAnimeId...");

        // Fetch anime details for both old and new anime
        $oldAnime = $duplicateAnimeService->getAnimeDetails($oldAnimeId);
        $newAnime = $duplicateAnimeService->getAnimeDetails($newAnimeId);

        if (!$oldAnime || !$newAnime) {
            $this->error('One or both of the anime IDs are invalid.');
            return;
        }

        // Display details of both anime
        $this->displayAnimeDetails($oldAnime, 'Old Anime');
        $this->displayAnimeDetails($newAnime, 'New Anime');

        // Confirm the merge with user input
        if (!$this->confirmMerge()) {
            $this->info('Merge operation cancelled.');
            return;
        }

        $this->info("Starting the process of merging anime ID $oldAnimeId into $newAnimeId.");

        $logger = function ($message) {
            $this->info($message);
        };

        // Use the service to merge the anime records
        $result = $duplicateAnimeService->mergeDuplicateAnime($oldAnimeId, $newAnimeId, $logger);

        if ($result['status'] === 'success') {
            $this->info($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * Display anime details.
     */
    private function displayAnimeDetails($anime, $label)
    {
        $this->info("---- $label ----");
        $this->info("ID: $anime->id");
        $this->info("Title: $anime->title");
        $this->info("Year: " . ($anime->year ?: 'UNKNOWN'));
        $this->info("Season: " . ($anime->season ?: 'UNKNOWN'));
        $this->info("Type: " . optional($anime->anime_type)->type ?: 'UNKNOWN');
        $this->info("Status: " . optional($anime->anime_status)->status ?: 'UNKNOWN');
        $this->info("Episodes: " . ($anime->episodes ?: 'UNKNOWN'));
        $this->info("Synonyms: " . ($anime->synonyms ?: 'NONE'));
        $this->info("--------------------");
    }

    /**
     * Confirm the merge by asking for user input.
     */
    private function confirmMerge()
    {
        $confirmation = $this->ask('Type "yes" to confirm the merge');

        return strtolower($confirmation) === 'yes';
    }
}
