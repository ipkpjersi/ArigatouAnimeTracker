<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use function App\Helpers\getBaseUrl;

class Anime extends Model
{
    protected $table = 'anime';

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
        'sources',
        'tags',
        'image_downloaded',
        'anime_type_id',
        'anime_status_id',
    ];

    // Set an ID used to hide all anime from public view.
    public static $HIDE_ALL_ANIME_PUBLICLY_ID = 5555;

    public function anime_type(): BelongsTo
    {
        return $this->belongsTo(AnimeType::class, 'anime_type_id');
    }

    public function anime_status(): BelongsTo
    {
        return $this->belongsTo(AnimeStatus::class, 'anime_status_id');
    }

    public function watch_status(): BelongsTo
    {
        return $this->belongsTo(WatchStatus::class, 'watch_status_id');
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('score', 'sort_order', 'progress', 'watch_status_id', 'notes', 'display_in_list', 'show_anime_notes_publicly')
            ->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AnimeReview::class, 'anime_id');
    }

    public static function getAnimeDetails($animeId)
    {
        return Anime::select('id', 'title', 'year', 'season', 'anime_type_id', 'anime_status_id', 'episodes', 'synonyms')
            ->with('anime_type', 'anime_status')
            ->find($animeId);
    }

    public static function getLocalPictureUrlFromAnimeId(int $animeId): string
    {
        $anime = static::find($animeId);
        if (!$anime) {
            return '';
        }
        return static::convertToLocalImageUrl($anime->picture, 'picture');
    }

    public static function getLocalThumbnailUrlFromAnimeId(int $animeId): string
    {
        $anime = static::find($animeId);
        if (!$anime) {
            return '';
        }
        return static::convertToLocalImageUrl($anime->thumbnail, 'thumbnail');
    }

    public function getLocalPictureUrl(): string
    {
        return static::convertToLocalImageUrl($this->picture, 'picture');
    }

    public function getLocalThumbnailUrl(): string
    {
        return static::convertToLocalImageUrl($this->thumbnail, 'thumbnail');
    }

    private static function convertToLocalImageUrl($url, $folder): string
    {
        // Get the base URL dynamically from Laravel
        $baseUrl = config('app.url'); // Fetch from .env (APP_URL)

        if (!empty($baseUrl)) {
            $baseUrl = getBaseUrl(); // Fetch from request if the base URL is still empty
        }

        // If the image is already local, return as is
        if (str_starts_with($url, $baseUrl)) {
            return $url;
        }

        // Parse the URL to get only the path after the domain
        $parsedUrl = parse_url($url, PHP_URL_PATH);

        // Remove leading slashes to prevent double slashes in final URL
        $relativePath = ltrim($parsedUrl, '/');

        // Construct the new local URL
        return url("$folder/$relativePath");
    }
}
