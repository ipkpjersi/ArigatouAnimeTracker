<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeUser extends Model
{

    protected $table = "anime_user";

    use HasFactory;

    protected $fillable = [
        'anime_id',
        'user_id',
        'watch_status_id',
        'score',
        'sort_order',
        'progress'
    ];

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function watchStatus()
    {
        return $this->belongsTo(WatchStatus::class);
    }

}
