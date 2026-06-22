<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class RescueReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'volunteer_id', 'latitude', 'longitude', 'location_address',
        'severity_level', 'animal_type', 'health_status', 'description', 'status'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function images()
    {
        return $this->hasMany(RescueReportImage::class);
    }
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class, 'volunteer_id');
    }

    public function backupRequests()
    {
        return $this->hasMany(BackupRequest::class, 'rescue_report_id');
    }

    public function rescueConsultations()
    {
        return $this->hasMany(RescueConsultation::class, 'rescue_report_id');
    }
    // public function auditLogs()
    // {
    //     return $this->hasMany(RescueAuditLog::class);
    // }
}
