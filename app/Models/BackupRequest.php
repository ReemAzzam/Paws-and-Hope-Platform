<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupRequest extends Model
{
    protected $fillable = [
        'rescue_report_id',
        'volunteer_id',
        'latitude',
        'longitude',
        'urgency_level',
        'reason',
        'status',
    ];

    public function rescueReport()
    {
        return $this->belongsTo(RescueReport::class, 'rescue_report_id');
    }

    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class, 'volunteer_id');
    }
}