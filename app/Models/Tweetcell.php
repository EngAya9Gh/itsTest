<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Tweetcell extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'section_id',
        'product_id',
        'name',
        'image', 
      'player_no',
        'image_url',
        'basic_price',
        'sale_price',
        'price',
        'status',
        'amount',
        'param',
      'qty_values',
        'note',
      'is-found',
    ];
 
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'tweetcell_orders','user_id', 'tweetcell_id');
    }
    
    public function tweetcellSection(): BelongsTo
    {
        return $this->belongsTo(TweetcellSection::class, 'section_id');
    }
    public function orders()
{
    return $this->hasMany(TweetcellOrder::class, 'tweetcell_id');
}






}
