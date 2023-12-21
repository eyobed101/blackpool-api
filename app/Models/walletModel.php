<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class walletModel extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'wallet_address', 'wallet_name', 'isCurrent', 'wallet_qr'];
}
