<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'dark_mode',
        'show_adult_content',
        'avatar',
        'anime_list_pagination_size',
        'show_anime_list_number',
        'registration_ip',
        'show_clear_anime_list_button',
        'display_anime_cards'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isModerator() {
        return $this->isAdmin() || $this->is_moderator;
    }

    public function anime() {
        return $this->belongsToMany(Anime::class)
                    ->withPivot('score', 'sort_order', 'progress', 'watch_status_id', 'notes', 'display_in_list')
                    ->withTimestamps();
    }

    public function animeStatistics() {
        $anime = $this->anime()->withPivot('score', 'watch_status_id', 'progress')->get();

        $totalCompleted = $anime->where('pivot.watch_status_id', WatchStatus::where('status', 'COMPLETED')->first()->id)->count();

        $totalEpisodes = $anime->sum('pivot.progress');
        $averageScore = $anime->where('pivot.score', '>', 0)->avg('pivot.score');

        $statuses = WatchStatus::pluck('status', 'id')->all();
        $animeStatusCounts = collect($statuses)->mapWithKeys(function ($status, $id) {
            return [$status => 0];
        });

        $animeCounts = $anime->groupBy('pivot.watch_status_id')->map->count();
        foreach ($animeCounts as $statusId => $count) {
            if (array_key_exists($statusId, $statuses)) {
                $animeStatusCounts[$statuses[$statusId]] = $count;
            }
        }

         $totalDaysWatched = ($totalEpisodes * 24) / (60 * 24);

        return compact('totalCompleted', 'totalEpisodes', 'averageScore', 'animeStatusCounts', 'totalDaysWatched');
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'user_friends', 'user_id', 'friend_user_id')
                    ->withTimestamps();
    }

    public function addFriend($friendId)
    {
        if ($this->id == $friendId) {
            throw new \Exception('You cannot add yourself as a friend.');
        }

        if ($this->friends()->where('friend_user_id', $friendId)->exists()) {
            throw new \Exception('This user is already your friend.');
        }

        $this->friends()->attach($friendId);
    }

    public function friendOf()
    {
        return $this->belongsToMany(User::class, 'user_friends', 'friend_user_id', 'user_id')
                    ->withTimestamps();
    }
}
