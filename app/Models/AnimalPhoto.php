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
        'caption',
        'order_number'
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
