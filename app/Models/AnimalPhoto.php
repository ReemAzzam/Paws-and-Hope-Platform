<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'photo_url',
        'is_main',
        'caption',
        'order_number'
    ];

    protected $appends = ['url'];

    protected $hidden = [
        'photo_url',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function getUrlAttribute()
{
    if (!$this->photo_url) {
        return null;
    }

    if (filter_var($this->photo_url, FILTER_VALIDATE_URL)) {
        return $this->photo_url;
    }

    $path = ltrim($this->photo_url, '/');
    $path = preg_replace('#^storage/#', '', $path);

    return asset('storage/' . $path);
}
}