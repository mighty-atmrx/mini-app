<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReviews extends Model
{
    use HasFactory;

    protected $table = 'user_reviews';

    protected $fillable = ['user_id', 'expert_id', 'rating', 'comment'];

    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
