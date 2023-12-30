<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class AppBackupRunCommand extends Command
{
    protected $signature = 'app:backup:run';
    protected $description = 'Alias for backup:run command';

    public function handle()
    {
        $this->info('Running backup:run command via app:backup:run...');
        Artisan::call('backup:run', [], $this->getOutput());
        $this->info('The backup:run command via app:backup:run has completed.');
    }
}
