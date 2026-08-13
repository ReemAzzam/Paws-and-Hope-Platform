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
        'currency',
        'gateway_type',
        'transaction_number',
        'receipt_image_path',
        'status',
        'rejection_reason',
        'is_anonymous',
    ];

    protected $appends  = [
        'receipt_image_url',
    ];
    protected $hidden = [
    'receipt_image_path',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

        public function getReceiptImageUrlAttribute()
    {
        if (!$this->receipt_image_path) {
            return null;
        }

        // إرجاع المسار المباشر المخزن في قاعدة البيانات
        return asset(ltrim($this->receipt_image_path, '/'));
    }
}