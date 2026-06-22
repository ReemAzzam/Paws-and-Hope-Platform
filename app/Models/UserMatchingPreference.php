<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMatchingPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'adoption_application_id',
        'preferred_animal_type',
        'preferred_age',
        'preferred_size',
        'housing_type',
        'activity_level',
        'hours_alone_daily',
        'children_status',
        'preferred_personality',
        'has_other_pets',
        'long_term_commitment',
        'matching_results',
        'highest_score'
    ];

    protected $casts = [
        'has_other_pets'       => 'boolean',
        'long_term_commitment' => 'boolean',
        'matching_results'     => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adoptionApplication()
    {
        return $this->belongsTo(AdoptionApplication::class);
    }
}
