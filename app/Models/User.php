<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'display_anime_cards',
        'enable_friends_system',
        'show_friends_on_profile_publicly',
        'show_friends_on_profile_when_logged_in',
        'show_friends_in_nav_dropdown',
        'show_friends_on_others_profiles',
        'enable_reviews_system',
        'show_reviews_when_logged_in',
        'show_reviews_publicly',
        'show_others_reviews',
        'show_reviews_in_nav_dropdown',
        'enable_score_charts_system',
        'enable_score_charts_own_profile_when_logged_in',
        'enable_score_charts_own_profile_publicly',
        'enable_score_charts_other_profiles',
        'show_anime_list_publicly',
        'show_clear_anime_list_sort_orders_button',
        'modifying_sort_order_on_detail_page_sorts_entire_list',
        'enable_favourites_system',
        'show_own_favourites_when_logged_in',
        'show_favourites_publicly',
        'show_favourites_in_nav_dropdown',
        'show_others_favourites',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isModerator()
    {
        return $this->isAdmin() || $this->is_moderator;
    }

    public function anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class)
            ->withPivot('score', 'sort_order', 'progress', 'watch_status_id', 'notes', 'display_in_list', 'show_anime_notes_publicly')
            ->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AnimeReview::class, 'user_id');
    }

    public function favourites()
    {
        return $this->belongsToMany(Anime::class, 'anime_favourites')
            ->withPivot('show_publicly', 'sort_order')
            ->withTimestamps();
    }

    public function animeStatistics()
    {
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

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_friends', 'user_id', 'friend_user_id')
            ->withPivot('show_friend_publicly')
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

    public function removeFriend($friendId)
    {
        if ($this->id == $friendId) {
            throw new \Exception('You cannot remove yourself as a friend.');
        }

        if (! $this->friends()->where('friend_user_id', $friendId)->exists()) {
            throw new \Exception('This user is already not your friend.');
        }

        $this->friends()->detach($friendId);
    }

    public function isFriend($userId)
    {
        return $this->friends()->where('friend_user_id', $userId)->exists();
    }

    public function toggleFriendPublicly($friendId)
    {
        // Check if the friend ID is the same as the user's ID
        if ($this->id == $friendId) {
            throw new \Exception('You cannot toggle yourself.');
        }

        // Find the friend relationship
        $friend = $this->friends()->where('friend_user_id', $friendId)->first();

        if (! $friend) {
            throw new \Exception('This user is not your friend.');
        }

        // Toggle the 'show_friend_publicly' status
        $friend->pivot->show_friend_publicly = ! $friend->pivot->show_friend_publicly;
        $friend->pivot->save();
    }

    public function passwordSecurity(): HasOne
    {
        return $this->hasOne(\App\Models\PasswordSecurity::class);
    }
}
