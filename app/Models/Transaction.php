<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id',      // المرسل
        'to_user_id',        // المستلم
        'amount', 
        'currency', 
        'payment_done',//-1 :cancel *****0:دين
        'order_id',
        'note', 
        'base_amount',
        'base_currency_id',
        'base_amount',       
        'remain_amount',
        'currency_id',
        'base_currency_id',
          ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * علاقة مع المستخدم المستلم (to_user_id)
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

   
}
