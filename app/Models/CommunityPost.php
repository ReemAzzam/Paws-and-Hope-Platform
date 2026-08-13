<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model
{
    protected $fillable = [
        'user_id',
        'animal_id',
        'category_id',
        'title',
        'content',
        'image_path'
    ];

    protected $appends = [
        'image_url',
    ];

    protected $hidden = [
        'image_path',
    ];

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(
            User::class,
            'post_likes',
            'post_id',
            'user_id'
        )->select('users.id', 'users.full_name');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        // إذا كان URL كامل
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // إزالة / من البداية
        $path = ltrim($this->image_path, '/');

        // منع storage/storage
        $path = preg_replace('#^storage/#', '', $path);

        return asset('storage/' . $path);
    }
}