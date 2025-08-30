<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;


class TweetcellKontor extends Model
{
    use HasFactory;
    protected $fillable = [
        'section_id',
        'name',
        'title',
        'code',
        'image', 
        'image_url',
        'basic_price',
        'price',
        'status',
        'kupur',
        'note',
        'sale_price',
        'type',
       'is_firsat',
    ];
 
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'tweetcell_kontor_orders','user_id', 'tweetcell_kontor_id');
    }
    
    public function tweetcellSection(): BelongsTo
    {
        return $this->belongsTo(TweetcellKontorSection::class, 'section_id');
    }
    public function orders()
{
    return $this->hasMany(TweetcellKontorOrder::class, 'tweetcell_kontor_id');
}

}
