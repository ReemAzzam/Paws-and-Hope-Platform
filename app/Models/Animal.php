<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'gender', 'age', 'size', 'weight',
        'description', 'story', 'health_status', 'is_vaccinated',
        'is_neutered', 'is_adoptable', 'is_urgent',
        'latitude', 'longitude', 'vet_id'
    ];

    public function vet()
    {
        return $this->belongsTo(Veterinarian::class, 'vet_id');
    }

    public function photos()
    {
        return $this->hasMany(AnimalPhoto::class);
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class);
    }

    public function behavioralAttributes()
    {
        return $this->hasMany(BehavioralAttribute::class);
    }

    public function adoptionApplications()
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    public function sponsorships()
    {
        return $this->hasMany(Sponsorship::class);
    }
}
