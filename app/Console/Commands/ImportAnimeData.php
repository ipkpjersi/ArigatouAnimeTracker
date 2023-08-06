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
            $json = file_get_contents(storage_path('app/imports/anime-offline-database.json'));
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $count = count($data['data']);
            $this->info("Importing " . $count . " anime records...");

            $startTime = microtime(true);

            foreach ($data['data'] as $animeData) {
                //Find or create related models like anime type, status, etc.
                $type = AnimeType::firstOrCreate(['type' => $animeData['type']]);
                if ($type->wasRecentlyCreated) {
                    \Log::info("New anime type created: " . $type->type);
                }
                $status = AnimeStatus::firstOrCreate(['status' => $animeData['status']]);
                if ($status->wasRecentlyCreated) {
                    \Log::info("New anime status created: " . $status->status);
                }
                $title = str_replace('"', '', $animeData['title']);

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
                    'tags' => implode(', ', $animeData['tags']),
                ]);
            }
        } catch (\Exception $e) {
            $this->error('An error occurred during import: ' . $e->getMessage());
            \Log::error($e);
            exit(1);
        }
        $duration = microtime(true) - $startTime;
        $this->info('Imported ' . $count . ' anime records successfully in ' . number_format($duration, 2) . ' seconds');
    }
}
