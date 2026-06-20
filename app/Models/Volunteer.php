<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'detailed_address',
        'age',
        'vol_type',
        'experience_level',
        'equipment',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'equipment' => 'array',
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
}
