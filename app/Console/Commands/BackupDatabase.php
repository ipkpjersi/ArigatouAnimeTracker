<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Back up the current database';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(DatabaseBackupService $backupService): void
    {
        $this->info('Starting database backup...');

        $logger = function ($message) {
            $this->info($message);
        };

        $backupPath = $backupService->backupDatabase($logger);

        if ($backupPath) {
            $this->info('Database backed up successfully.');
        } else {
            $this->error('Failed to back up the database.');
        }
    }
}
