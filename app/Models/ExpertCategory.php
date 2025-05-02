<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertCategory extends Model
{
    use HasFactory;
    use Filterable;

    protected $table = 'expert_categories';
    protected $fillable = [
      'expert_id', 'category_id'
    ];
}
