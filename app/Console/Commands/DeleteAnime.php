<?php

namespace App\Console\Commands;

use App\Models\Anime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteAnime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-anime {animeId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an anime entry by its ID after confirmation';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $animeId = $this->argument('animeId');

        $this->info("Fetching details for anime ID $animeId...");

        // Fetch the anime details
        $anime = Anime::getAnimeDetails($animeId);

        if (! $anime) {
            $this->error('Anime ID is invalid or does not exist.');

            return;
        }

        // Display details of the anime
        $this->displayAnimeDetails($anime);

        // Confirm the deletion with user input
        if (! $this->confirmDelete()) {
            $this->info('Delete operation cancelled.');

            return;
        }

        $this->info("Deleting anime ID $animeId...");

        try {
            DB::beginTransaction();

            // Delete references in anime_user table
            DB::table('anime_user')->where('anime_id', $animeId)->delete();
            $this->info('Deleted references from anime_user table.');

            // Delete references in anime_reviews table
            DB::table('anime_reviews')->where('anime_id', $animeId)->delete();
            $this->info('Deleted references from anime_reviews table.');

            // Delete the anime record itself
            DB::table('anime')->where('id', $animeId)->delete();
            $this->info("Deleted anime record with ID $animeId.");

            DB::commit();

            $this->info("Anime ID $animeId has been successfully deleted.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred while deleting anime ID $animeId: ".$e->getMessage());
            Log::error("Error deleting anime ID $animeId: ".$e->getMessage());
        }
    }

    /**
     * Display anime details.
     */
    private function displayAnimeDetails($anime)
    {
        $this->info('---- Anime Details ----');
        $this->info("ID: $anime->id");
        $this->info("Title: $anime->title");
        $this->info('Year: '.($anime->year ?? 'UNKNOWN'));
        $this->info('Season: '.($anime->season ?? 'UNKNOWN'));
        $this->info('Type: '.$anime->anime_type?->type ?: 'UNKNOWN');
        $this->info('Status: '.$anime->anime_status?->status ?: 'UNKNOWN');
        $this->info('Episodes: '.($anime->episodes ?? 'UNKNOWN'));
        $this->info('Synonyms: '.($anime->synonyms ?? 'NONE'));
        $this->info('-----------------------');
    }

    /**
     * Confirm the deletion by asking for user input.
     */
    private function confirmDelete()
    {
        $confirmation = $this->ask('Type "yes" to confirm the deletion');

        return strtolower($confirmation) === 'yes';
    }
}
