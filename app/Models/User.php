<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'telegram_user_id',
        'first_name',
        'last_name',
        'birthdate',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [

    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [

        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'telegram_user_id' => $this->telegram_user_id,
            'first_name' => $this->first_name
        ];
    }

    public function favorites()
    {
        return $this->belongsToMany(Expert::class, 'favorites', 'user_id', 'expert_id');
    }

    public function reviews()
    {
        return $this->hasMany(UserReviews::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
