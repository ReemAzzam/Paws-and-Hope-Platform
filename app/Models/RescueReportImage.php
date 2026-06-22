<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RescueReportImage extends Model
{
    protected $fillable = ['rescue_report_id', 'image_path'];

    public function rescueReport()
    {
        return $this->belongsTo(RescueReport::class, 'rescue_report_id');
    }
}