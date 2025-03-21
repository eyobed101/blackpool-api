<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'verification_status',
        'balance',
        'role',
        'admin_id',
        'profile_picture'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function verification()
    {
        return $this->hasOne(Verification::class);
    }

    public function bet()
    {
        return $this->hasMany(Bet::class, 'user_id', 'id');
    }

    public function betCombinations()
    {
        return $this->hasMany(BetCombination::class);
    }
    
    public function transaction() 
    {
        return $this->hasMany(Transaction::class, 'user_id', 'id');
    }
    public function admin()
    {
         return $this->belongsTo(self::class, 'admin_id');
    }

}

