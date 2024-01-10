<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
          'first_name', 'last_name', 'date_of_birth',
          'profil_picture', 'address', 'address2','postalcode', 'city', 'province',
          'country', 'user_id', 'isVerified'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


