<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'detailed_address',
        'age',
        'vol_type',
        'experience_level',
        'equipment',
        'current_latitude',
        'current_longitude',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'equipment'         => 'array',
        'current_latitude'  => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'is_approved'       => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rescueReports()
    {
        return $this->hasMany(RescueReport::class, 'assigned_volunteer_id');
    }

    public function backupRequests()
    {
        return $this->hasMany(BackupRequest::class, 'volunteer_id');
    }

    public function rescueConsultations()
    {
        return $this->hasMany(RescueConsultation::class, 'volunteer_id');
    }
}
