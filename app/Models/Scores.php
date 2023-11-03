<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scores extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_key',
        'sport_title',
        'commence_time',
        'completed',
        'home_team',
        'away_team',
        'scores',
        'last_update',
    ];
}
