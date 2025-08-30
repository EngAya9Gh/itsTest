<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferMoneyFirm extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'image_url',
        'iban',
        'account_name',
        'image',
        'status',
        'note'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'transfer_money_firm_orders', 'transfer_money_firm_id', 'user_id');
    }
    public function orders()
    {
        return $this->hasMany(TransferMoneyFirmOrder::class, 'transfer_money_firm_id');
    }
    
}
