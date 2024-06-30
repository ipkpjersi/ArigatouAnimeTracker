<?php

namespace App\Console\Commands;

use App\Models\InviteCode;
use Illuminate\Console\Command;

class RevokeUnusedInviteCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:revoke-unused-invite-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revokes any unused invite codes.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {

        $deletedCount = InviteCode::where('used', false)->delete();

        $this->info("Successfully deleted {$deletedCount} unused invite codes.");

        return $deletedCount;
    }
}
