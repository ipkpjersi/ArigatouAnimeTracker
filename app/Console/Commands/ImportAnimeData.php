<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\AnimeStatus;
use App\Models\AnimeType;
use Illuminate\Console\Command;

class ImportAnimeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-anime-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime from the anime database JSON file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting anime data import...");
        try {
            $count = 0;
            $json = file_get_contents(storage_path('app/imports/anime-offline-database.json'));
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $total = count($data['data']);
            $this->info("Importing " . $total . " anime records...");

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

                //Skip the anime if it exists.
                if ($existingAnime) {
                    $this->info("Skipping existing anime: " . $title);
                    continue;
                }

                //Find or create related models like anime type, status, etc.
                $type = AnimeType::where('type', $animeData['type'])->firstOrCreate(['type' => $animeData['type']]);
                if ($type->wasRecentlyCreated) {
                    $this->info("New anime type created: " . $type->type);
                    \Log::info("New anime type created: " . $type->type);
                }
                $status = AnimeStatus::where('status', $animeData['status'])->firstOrCreate(['status' => $animeData['status']]);
                if ($status->wasRecentlyCreated) {
                    $this->info("New anime status created: " . $status->status);
                    \Log::info("New anime status created: " . $status->status);
                }

                //Create the anime record
                $anime = Anime::create([
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
        } catch (\Exception $e) {
            $this->error('An error occurred during import: ' . $e->getMessage());
            \Log::error($e);
            exit(1);
        }
        $duration = microtime(true) - $startTime;
        $this->info("Imported $count anime records successfully (out of $total anime records) in " . number_format($duration, 2) . " seconds");
    }
}
