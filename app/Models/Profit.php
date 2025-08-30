<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profit extends Model
{
    use HasFactory;

    const TYPES = [
        'ProgramOrder',
        'CardOrder',
        'EcardOrder',
        'DataCommunicationOrder',
        'TransferOrder',
        'TurkificationOrder',
        'GameOrder',
        'AppOrder',
        'ServiceOrder',
        'EbankOrder'
    ];
    protected $fillable = [
        'id_order',
        'type_order',
        'user_id',
      'currency_id',
     'user_currency_id',
      'user_rate_at_time',
        'profit_amount',
    ];

    // علاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function profitable()
    {
        return $this->morphTo();
    }
}
