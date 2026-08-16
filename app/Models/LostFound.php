<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LostFoundPhoto;
//use Illuminate\Database\Eloquent\SoftDeletes;

class LostFound extends Model
{
    use HasFactory;

    protected $table = 'lost_found';

    protected $fillable = [
        'user_id', 'post_type','status', 'animal_type', 'name', 'breed', 'gender',
        'size', 'age', 'color', 'description','contact_email','contact_phone', 'location_description',
        'latitude', 'longitude', 'contact_phone', 'image_url', 'status',
        'distinctive_marks', 'collar_tags', 'microchipped', 'neutered',
        'temperament', 'views' , 'incident_at'
    ];

    protected $casts = [
        'latitude'     => 'decimal:8',
        'longitude'    => 'decimal:8',
        'microchipped' => 'boolean',
        'neutered'     => 'boolean',
        'views'        => 'integer',
        'incident_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function views()
    {
    return $this->hasMany(LostFoundView::class);
    }

    public function matchesAsLost()
    {
        return $this->hasMany(LostFoundMatch::class, 'lost_post_id');
    }

    public function matchesAsFound()
    {
        return $this->hasMany(LostFoundMatch::class, 'found_post_id');
    }

    public function photos()
    {
        return $this->hasMany(LostFoundPhoto::class, 'lost_found_id')
                ->orderBy('order_number');
    }

}
