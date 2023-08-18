<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeType extends Model
{
    protected $table = 'anime_type';

    use HasFactory;

    protected $guarded = [];
}
