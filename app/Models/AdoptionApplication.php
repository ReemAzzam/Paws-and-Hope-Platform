<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdoptionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'animal_id', 'reason_for_adoption', 'has_other_pets',
        'other_pets_info', 'housing_type', 'has_garden', 'family_members_count',
        'children_under_10', 'work_schedule', 'experience_with_animals',
        'commitment_declaration', 'emergency_contact_name',
        'emergency_contact_phone', 'status', 'approved_at', 'approved_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
