<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorshipPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsorship_id', 'amount', 'payment_method', 'transaction_number', 'receipt_image_url', 'verification_status', 'verified_by', 'verified_at', 'rejection_reason'
    ];

    public function sponsorship()
    {
        return $this->belongsTo(Sponsorship::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}