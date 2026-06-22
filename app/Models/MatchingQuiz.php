<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchingQuiz extends Model
{
    use HasFactory;

    // مهم: تحديد اسم الجدول يدوياً
    protected $table = 'matching_quiz';

    protected $fillable = [
        'step_id',
        'question_order',
        'question_text',
        'options',
        'key',
        'hint',
        'type'
    ];

    protected $casts = [
        'options' => 'array'
    ];
}
