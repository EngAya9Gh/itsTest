<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class ServiceOrder extends Model
{
   
   /* 1: imei
  2:  dfth new   username ,password,email, note
    3:  dfth old   username,note
   5: chemra username ,password,note
  4:  aftar count ,email  note
  6:kimlik ,line_photo note
  7:kimlik ,line_photo,mobile note
  8:count
*/
    protected $fillable = ['user_id','reject_reason', 'service_id', 'price', 'basic_price', 'status', 'count','username'
                           ,'note','email','password','ime',
                           'kimlik','line_photo','mobile','last_mobile'];
   public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function profits()
    {
        return $this->morphMany(Profit::class, 'profitable');
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
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
