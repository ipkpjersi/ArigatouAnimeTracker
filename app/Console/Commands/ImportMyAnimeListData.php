<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MyAnimeListImportService;

class ImportMyAnimeListData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-myanimelist-data {userId} {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports anime list from a MyAnimeList XML file for a user.';

    /**
     * Execute the console command.
     *
     * @param MyAnimeListImportService $importer
     * @return void
     */
    public function handle(MyAnimeListImportService $importer)
    {
        $userId = $this->argument('userId');
        $filePath = $this->argument('filePath');

        $this->info("Starting MyAnimeList data import for user ID $userId...");

        try {
            $xmlContent = file_get_contents($filePath);
            $result = $importer->import($xmlContent, $userId);
            $duration = round($result['duration'], 2);

            $this->info("Imported {$result['count']} out of {$result['total']} anime records successfully in {$duration} seconds");

        } catch (\Exception $e) {
            $this->error('An error occurred during import: ' . $e->getMessage());
        }
    }
}
