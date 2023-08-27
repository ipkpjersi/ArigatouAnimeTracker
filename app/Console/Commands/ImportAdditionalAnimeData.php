<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportAdditionalAnimeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-anime-additional-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports additional anime data from the generated SQL file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to import additional anime data from SQL file...');

        $sqlPath = database_path('seeders/anime_additional_data.sql');

        if (File::exists($sqlPath)) {
            $sql = File::get($sqlPath);
            DB::unprepared($sql);
            $this->info('Imported additional anime data successfully.');
            return 0; // Success
        } else {
            $this->error('SQL file does not exist.');
            return 1; // Error
        }
    }
}
