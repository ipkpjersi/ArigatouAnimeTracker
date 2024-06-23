<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\ConsoleOutput;

class AppBackupRunCommand extends Command
{
    protected $signature = 'app:backup:run';

    protected $description = 'Alias for backup:run command. This is the recommended backup command.';

    public function handle(): void
    {
        $this->info('Running backup:run command via app:backup:run...');
        Artisan::call('backup:run', [], new ConsoleOutput);
        $this->info('The backup:run command via app:backup:run has completed.');
    }
}
