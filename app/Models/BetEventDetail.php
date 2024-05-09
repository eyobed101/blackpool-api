<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BetEventDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $fillable = [
        'id', 'bet_id', 'sport', 'price', 'type', 'team', 'vs', 'home', 'away', 'commence_time'
    ];

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }}
