<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwarenessPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'veterinarian_id',
        'title',
        'content',
        'image_url'
    ];


    protected $appends = [
        'image_full_url',
    ];

    public function getImageFullUrlAttribute()
{
    if (!$this->image_url) {
        return null;
    }

    $path = ltrim($this->image_url, '/');

    if (str_starts_with($path, 'storage/')) {
        return asset($path);
    }

    return asset('storage/' . $path);
}


    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class, 'veterinarian_id');
    }
    public function awarenessPosts()
{
    return $this->hasMany(AwarenessPost::class, 'veterinarian_id');
}

    public function likes()
    {
        return $this->hasMany(AwarenessPostLike::class, 'awareness_post_id');
    }

    // تأكدي من وجود هذه الدالة هنا وحفظ الملف!
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'awareness_post_likes', 'awareness_post_id', 'user_id')
                    ->select('users.id', 'users.full_name')
                    ->withTimestamps();
    }
}
