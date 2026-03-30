<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'anime_id', 'show_publicly', 'sort_order'])]
class AnimeFavourite extends Model
{
    use HasFactory;

    /**
     * Get the user that added the favourite.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the anime that this favourite belongs to.
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }
}
