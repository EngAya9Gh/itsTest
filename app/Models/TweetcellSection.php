<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TweetcellSection extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'name',
      'increase_percentage',
        'image',
        'type',
        'image_url',
        'status',  
      'is-found',
      'api_no',
    ];

    public function tweetcells(): HasMany
    {
        return $this->hasMany(Tweetcell::class, 'section_id');
    }
}
