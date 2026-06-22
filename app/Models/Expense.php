<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'amount',
        'title',
        'description',
        'category',
        'invoice_image_path',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}