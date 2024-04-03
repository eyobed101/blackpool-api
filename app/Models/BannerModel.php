<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerModel extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'banner_alt', 'isActive', 'banner_image', 'banner_location'];
}
