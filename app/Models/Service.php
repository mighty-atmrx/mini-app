<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    use Filterable;

    protected $table = 'services';
    protected $fillable = ['expert_id', 'title', 'description', 'price', 'category_id',];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}
