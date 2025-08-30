<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TweetcellOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'tweetcell_id',
      'player_no',
        'name',
      'reject_reason',
        'count',
       'kupur'   ,
      'basic_price',
        'price',
        'note',
        'status',
      'device_info'
    ];
    
    public function profits()
    {
        return $this->morphMany(Profit::class, 'profitable');
 
    }

  
   protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = Str::uuid()->toString();  // إنشاء UUID
            }
        });
    }
  
  
}
