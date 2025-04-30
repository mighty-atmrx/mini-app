<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['id', 'category'];
    protected $dates = ['created_at', 'updated_at'];

    public function experts()
    {
        return $this->belongsToMany(Expert::class, 'expert_categories', 'category_id', 'expert_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Service::class);
    }
}
