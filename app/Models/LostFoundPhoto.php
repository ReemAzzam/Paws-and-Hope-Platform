<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostFoundPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'lost_found_id',
        'photo_url',
        'is_main',
        'order_number'
    ];

    public function lostFound()
    {
        return $this->belongsTo(LostFound::class, 'lost_found_id');
    }
}
