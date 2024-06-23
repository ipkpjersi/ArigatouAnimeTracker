<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeReview extends Model
{
    use HasFactory;

    // Define fillable attributes if needed
    protected $fillable = [
        'user_id',
        'anime_id',
        'title',
        'body',
        'show_review_publicly',
        'recommendation',
        'contains_spoilers',
    ];

    /**
     * Get the user that authored the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the anime that this review belongs to.
     */
    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }
}
