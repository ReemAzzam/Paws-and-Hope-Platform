<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'vaccine_name',
        'vaccine_type',
        'vaccination_date',
        'notes'
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
