<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veterinarian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'professional_name',
        'specialization',
        'clinic_location',
        'license_number',
        'working_hours',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function animals()
    {
        return $this->hasMany(Animal::class, 'vet_id');
    }
}
