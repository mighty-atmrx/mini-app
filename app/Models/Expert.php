<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;
    use Filterable;

    protected $table = 'experts';
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'profession', 'biography',
        'photo', 'experience', 'education', 'rating'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'expert_categories', 'expert_id', 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoAttribute($value)
    {
        return $value ? url($value) : null;
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'expert_id', 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(ExpertReview::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
