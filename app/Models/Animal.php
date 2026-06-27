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
    'is_neutered', 'availability_status', 'is_urgent',
    'latitude', 'longitude', 'vet_id', 'rescue_report_id'
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

    // جلب الكفالة النشطة الحالية فقط للحيوان (إن وجدت)
    public function activeSponsorship()
    {
        return $this->hasOne(Sponsorship::class, 'animal_id')->where('status', 'active');
    }

    // جلب الكفيل الحالي مباشرة (مفيد للوحة تحكم الإدارة)
    public function currentSponsor()
    {
        return $this->hasOneThrough(
            User::class,
            Sponsorship::class,
            'animal_id',
            'id',
            'id',
            'user_id'
        )->where('sponsorships.status', 'active');
    }


    public function updates()
    {
        return $this->hasMany(AnimalUpdate::class);
    }
}
