<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeStatus extends Model
{
    protected $table = 'anime_status';

    use HasFactory;

    protected $guarded = [];

    protected $id = "status_id";
}
