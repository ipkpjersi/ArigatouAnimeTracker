<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AnimeListImportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-myanimelist-data {username} {filePath}')]
#[Description('Imports anime list from a MyAnimeList XML file for a user.')]
class ImportMyAnimeListData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AnimeListImportService $importer): void
    {
        $username = $this->argument('username');
        $filePath = $this->argument('filePath');
        $user = User::where('username', '=', $username)->first();
        if ($user === null) {
            $this->error("User $username not found!");

            return;
        }
        $userId = $user->id;
        $importType = 'myanimelist';
        $this->info("Starting MyAnimeList data import for user $username (ID $userId)...");

        try {
            $xmlContent = file_get_contents($filePath);

            $logger = function ($message) {
                $this->info($message);
            };

            $result = $importer->import($xmlContent, $importType, $userId, $logger);
            $duration = round($result['duration'], 2);

            $this->info("Imported {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds");

        } catch (\Exception $e) {
            $this->error('An error occurred during import: '.$e);
        }
    }
}
