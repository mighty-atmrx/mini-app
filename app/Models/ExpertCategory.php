<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertCategory extends Model
{
    protected $table = 'expert_categories';
    protected $fillable = [
      'expert_id', 'category_id'
    ];
}
