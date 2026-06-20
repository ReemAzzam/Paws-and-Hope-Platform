<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RescueReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'assigned_volunteer_id',
        'animal_id',
        'animal_type',
        'approximate_condition',
        'image_url',
        'latitude',
        'longitude',
        'severity_level',
        'current_status'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class, 'assigned_volunteer_id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    // public function auditLogs()
    // {
    //     return $this->hasMany(RescueAuditLog::class);
    // }
}
