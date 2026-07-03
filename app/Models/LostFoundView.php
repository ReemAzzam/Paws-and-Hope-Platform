<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LostFound;

class LostFoundView extends Model
{
    protected $fillable = [
        'lost_found_id',
        'user_id',
        'ip_address',
    ];

    public function lostFound()
    {
        return $this->belongsTo(LostFound::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
