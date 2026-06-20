<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostFound extends Model
{
    use HasFactory;

    protected $table = 'lost_found';

    protected $fillable = [
        'user_id',
        'post_type',
        'animal_type',
        'description',
        'location_description',
        'latitude',
        'longitude',
        'contact_phone',
        'image_url',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
