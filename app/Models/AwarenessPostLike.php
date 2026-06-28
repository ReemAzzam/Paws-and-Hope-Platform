<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwarenessPostLike extends Model
{
    protected $fillable = ['user_id', 'awareness_post_id'];
}