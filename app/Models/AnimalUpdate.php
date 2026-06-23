<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalUpdate extends Model
{
    use HasFactory;

    protected $fillable = ['animal_id', 'title', 'content', 'media_url', 'type'];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}