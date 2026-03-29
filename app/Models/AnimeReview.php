<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'anime_id', 'title', 'body', 'show_review_publicly', 'recommendation', 'contains_spoilers'])]
class AnimeReview extends Model
{
    use HasFactory;

    /**
     * Get the user that authored the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the anime that this review belongs to.
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }
}
