<?php
namespace App\Console\Commands;

use Spatie\Backup\Commands\BackupCommand;

class CustomBackupCommand extends BackupCommand
{
    //Here we maintain the same signature but modify the default for --disable-notifications
    protected $signature = 'backup:run {--filename=} {--only-db} {--db-name=*} {--only-files} {--only-to-disk=} {--disable-notifications=true} {--timeout=} {--tries=}';

    protected $description = 'Run the spatie laravel-backup with notifications disabled by default.';

    public function handle(): int
    {
        //Check the value of the --disable-notifications option because we set a default option for a boolean, which only checks presence not value in Laravel.
        if ($this->option('disable-notifications') !== 'false') {
            $this->input->setOption('disable-notifications', true);
        } else {
            $this->input->setOption('disable-notifications', false);
        }
        //Call the parent handle method since we don't want to modify any underlying logic.
        return parent::handle();
    }
}
