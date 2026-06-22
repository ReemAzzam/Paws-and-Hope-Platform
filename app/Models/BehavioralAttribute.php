<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehavioralAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'attribute_name',
        'intensity',
        'description'
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
