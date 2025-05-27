<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';
    protected $fillable = ['expert_id', 'service_id', 'user_id', 'date', 'time', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
