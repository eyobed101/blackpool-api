<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction() : HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
