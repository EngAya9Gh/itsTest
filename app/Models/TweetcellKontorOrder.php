<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class TweetcellKontorOrder extends Model
{
   
    use HasFactory;
    protected $fillable = [
        'user_id',
        'order_id',
        'tweetcell_kontor_id',
        'mobile',
      'basic_price',
        'price',
        'note',
        'count',
        'status',
      'reject_reason',
      'admin_reject_reason',
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
