<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('anime_user')]
#[Fillable(['anime_id', 'user_id', 'watch_status_id', 'score', 'sort_order', 'progress', 'notes', 'display_in_list', 'show_anime_notes_publicly'])]
class AnimeUser extends Model
{
    use HasFactory;

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function watch_status(): BelongsTo
    {
        return $this->belongsTo(WatchStatus::class);
    }
}
