<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model {
    protected $fillable = ['name_en', 'slug', 'icon'];

    public function posts() {
        return $this->hasMany(CommunityPost::class, 'category_id');
    }
}
