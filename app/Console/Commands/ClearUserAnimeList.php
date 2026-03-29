<?php

namespace App\Console\Commands;

use App\Models\AnimeUser;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:clear-user-animelist {username : The username of the user}')]
#[Description('Clears all anime list entries for a given user.')]
class ClearUserAnimeList extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $username = $this->argument('username');

        // Find the user by username
        $user = User::where('username', $username)->first();

        if (! $user) {
            $this->error("User with username {$username} not found.");

            return;
        }

        $this->info("Starting to clear anime list for user {$username}...");

        try {
            // Remove all anime list entries for the user
            AnimeUser::where('user_id', $user->id)->delete();

            $this->info("Anime list for user {$username} has been cleared.");
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e);
        }
    }
}
