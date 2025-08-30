<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FaturaOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'fatura_id',
      'fatura_no',
        'name',
      'reject_reason',
        'count',
       'mobile',
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
