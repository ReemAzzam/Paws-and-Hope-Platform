<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalMedicalCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'condition',
        'treatment',
        'start_date',
        'end_date',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
