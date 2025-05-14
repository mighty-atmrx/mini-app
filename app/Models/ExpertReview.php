<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertReview extends Model
{
    protected $table = 'expert_reviews';

    protected $fillable = ['user_id', 'expert_id', 'rating', 'comment'];

    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}
