<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    protected $table = 'experts';
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'biography',
        'photo', 'experience', 'education'
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

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
