<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $fillable = ['id','user_id', "bet_combination_id","bet_type","event_id","outcome","bet_amount","potential_payout","status"];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function transaction() 
    {
        return $this->hasMany(Transaction::class);
    }

    public function betCombination()
    {
        return $this->belongsTo(BetCombination::class);
    }
}
