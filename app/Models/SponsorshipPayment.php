<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorshipPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsorship_id',
        'amount',
        'currency',
        'payment_method',
        'transaction_number',
        'receipt_image_url',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $appends = ['receipt_url'];

    protected $hidden = [
        'receipt_image_url',
    ];

    public function sponsorship()
    {
        return $this->belongsTo(Sponsorship::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getReceiptUrlAttribute()
    {
        if (!$this->receipt_image_url) {
            return null;
        }

        // إذا كانت القيمة أصلاً URL كامل
        if (filter_var($this->receipt_image_url, FILTER_VALIDATE_URL)) {
            return $this->receipt_image_url;
        }

        // إزالة / من البداية
        $path = ltrim($this->receipt_image_url, '/');

        // منع تكرار storage/storage
        $path = preg_replace('#^storage/#', '', $path);

        return asset('storage/' . $path);
    }
}