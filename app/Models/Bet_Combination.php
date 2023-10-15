<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BetCombination extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
