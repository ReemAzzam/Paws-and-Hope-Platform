<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'animal_id', 
        'monthly_amount', 
        'status', 
        'start_date', 
        'next_payment_due', 
        'notes'
    ];

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // الكفالة تابعة لحيوان محدد
    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    // الكفالة لها العديد من الدفعات المالية التاريخية
    public function payments()
    {
        return $this->hasMany(SponsorshipPayment::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
