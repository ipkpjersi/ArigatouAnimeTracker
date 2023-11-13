<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{

    protected $table = "anime";

    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'episodes',
        'status',
        'season',
        'year',
        'picture',
        'thumbnail',
        'synonyms',
        'relations',
        'tags',
        'image_downloaded'
    ];

    public function anime_type()
    {
        return $this->belongsTo(AnimeType::class, 'anime_type_id');
    }

    public function anime_status()
    {
        return $this->belongsTo(AnimeStatus::class, 'anime_status_id');
    }

    public function watch_status()
    {
        return $this->belongsTo(WatchStatus::class, 'watch_status_id');
    }

    public function user()
    {
       return $this->belongsToMany(User::class)
                    ->withPivot('score', 'sort_order', 'progress', 'watch_status_id', 'notes', 'display_in_list', 'show_anime_notes_publicly')
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(AnimeReview::class, 'anime_id');
    }
}
