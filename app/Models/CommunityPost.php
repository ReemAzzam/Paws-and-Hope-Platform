<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model {
    protected $fillable = ['user_id', 'animal_id', 'category_id', 'title', 'content', 'image_path'];

    // العلاقة مع التصنيف
    public function category() {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    // العلاقة مع الحيوان
    public function animal() {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function likedByUsers() {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'user_id')
                    // جلب الـ id والاسم فقط في الوقت الحالي
                    ->select('users.id', 'users.full_name'); 
    }

    // علاقة لحساب عدد اللايكات
    public function likes() {
        return $this->hasMany(PostLike::class, 'post_id');
    }
}