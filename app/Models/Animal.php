<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'gender', 'age','age_recorded_at', 'size', 'weight',
        'description', 'story', 'health_status', 'is_vaccinated',
        'is_neutered', 'is_adoptable', 'availability_status','reserved_until', 'is_urgent',
         'vet_id', 'rescue_report_id'
    ];

    protected $casts = [
    'age_recorded_at' => 'datetime',
    'reserved_until' => 'datetime',
    ];


  public function getAgeAttribute($value)
    {
        if ($value === null || !$this->age_recorded_at) {
            return $value;
        }

        $recordedAt = $this->age_recorded_at;
        $today = now();

        $years = $recordedAt->diffInYears($today);

        return (int) $value + (int) $years;
    }

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

    public function medicalConditions()
    {
       return $this->hasMany(AnimalMedicalCondition::class);
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

    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class, 'animal_id');
    }
}
