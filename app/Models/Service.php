<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'courses';
    protected $fillable = ['expert_id', 'title', 'description', 'price', 'category_id',];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
