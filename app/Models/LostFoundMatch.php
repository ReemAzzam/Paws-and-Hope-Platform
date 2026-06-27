<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostFoundMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'lost_post_id',
        'found_post_id',
        'match_score',
        'match_reasons',
        'status',
        'notified_at'
    ];

    protected $casts = [
        'match_reasons' => 'array',
        'notified_at'   => 'datetime'
    ];

    public function lostPost()
    {
        return $this->belongsTo(LostFound::class, 'lost_post_id');
    }

    public function foundPost()
    {
        return $this->belongsTo(LostFound::class, 'found_post_id');
    }

    public function lostUser()
    {
        return $this->lostPost->user();
    }

    public function foundUser()
    {
        return $this->foundPost->user();
    }
}
