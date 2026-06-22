<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RescueConsultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rescue_report_id',
        'volunteer_id',
        'veterinarian_id',
        'question',
        'medical_advice',
        'status'
    ];

    public function rescueReport()
    {
        return $this->belongsTo(RescueReport::class);
    }

    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class);
    }
}