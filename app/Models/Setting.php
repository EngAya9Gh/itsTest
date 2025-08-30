<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
    'app_name',
    'logo',
    'logo_url',
    'about_text',
    'mobile',
    'address',
    'email',
    'facebook',
    'instagram',
    'twitter',
    'telegram',
    'tweetcell_password',
    'tweetcell_user_name',  
    'znet_password',
    'znet_user_name',
      
    ];
}
