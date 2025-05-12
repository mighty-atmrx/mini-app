<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = ['expert_id', 'service_id', 'user_id', 'date', 'time', 'status'];
}
