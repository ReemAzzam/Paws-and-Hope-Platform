<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'gateway_type',
        'transaction_number',
        'receipt_image_path',
        'status',
        'rejection_reason',
        'is_anonymous',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}