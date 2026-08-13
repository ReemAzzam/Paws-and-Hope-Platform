<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $guard_name = 'api';
   protected $fillable = [
    'full_name',
    'email',
    'password',
    'photo',
    'country_code',
    'phone_number',
    'governorate',
    'latitude',
    'longitude',
    'account_status',
    'two_factor_enabled',
    'email_verified_at',
    'google_id',
];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'latitude'          => 'decimal:8',
        'longitude'         => 'decimal:8',
        'two_factor_enabled'=> 'boolean',
    ];

    protected $appends = [
    'photo_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'photo',
    ];

protected static function booted()
{
    static::deleting(function ($user) {

        if ($user->photo &&
            Storage::disk('public')->exists($user->photo)) {

            Storage::disk('public')->delete($user->photo);
        }

    });
}


    // ====================== Relations ======================

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function veterinarian()
    {
        return $this->hasOne(Veterinarian::class);
    }

    public function volunteer()
    {
        return $this->hasOne(Volunteer::class);
    }

    public function regularUser()
    {
        return $this->hasOne(RegularUser::class);
    }

    public function rescueReports()
    {
        return $this->hasMany(RescueReport::class, 'user_id');
    }

    public function assignedRescues()
    {
        return $this->hasMany(RescueReport::class, 'assigned_volunteer_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function sponsorships()
    {
        return $this->hasMany(Sponsorship::class);
    }

    public function adoptionApplications()
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    public function lostFoundPosts()
    {
        return $this->hasMany(LostFoundMatch::class);
    }


    public function recordedExpenses()
    {
        return $this->hasMany(Expense::class, 'admin_id');
    }

    public function sponsoredAnimals()
    {
        return $this->hasManyThrough(
            Animal::class,
            Sponsorship::class,
            'user_id',     // المفتاح الخارجي في جدول الكفالات
            'id',          // المفتاح المحلي في جدول الحيوانات
            'id',          // المفتاح المحلي في جدول المستخدمين
            'animal_id'    // المفتاح الخارجي في جدول الكفالات الموجه للحيوان
        )->where('sponsorships.status', 'active'); // ترشيح الكفالات النشطة فقط
    }

    public function generalConsultations()
    {
        return $this->hasMany(GeneralConsultation::class, 'user_id');
    }

    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class, 'user_id');
    }

    public function likedPosts()
    {
        return $this->belongsToMany(CommunityPost::class, 'post_likes', 'user_id', 'post_id')
                    ->withTimestamps();
    }

    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return null;
        }

        // إذا كانت الصورة URL كامل
        if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
            return $this->photo;
        }

        // إزالة / من البداية
        $path = ltrim($this->photo, '/');

        // منع storage/storage
        $path = preg_replace('#^storage/#', '', $path);

        return asset('storage/' . $path);
    }
}
