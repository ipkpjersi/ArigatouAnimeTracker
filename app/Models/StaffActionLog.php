<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffActionLog extends Model
{
    protected $table = 'staff_action_log';

    use HasFactory;

    protected $fillable = ['user_id', 'target_id', 'action', 'message'];
}
