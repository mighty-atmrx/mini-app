<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertsSchedule extends Model
{
    protected $table = 'experts_schedules';
    protected $fillable = ['expert_id', 'date', 'time'];

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }
}
