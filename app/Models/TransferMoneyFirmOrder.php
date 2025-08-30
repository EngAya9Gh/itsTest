<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferMoneyFirmOrder extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'transfer_money_firm_id',
        'user_id',
        'sender',
        'value',
        'amount',//القيمة الفعلية
      'remain_amount',
      'currency',
        'dekont_no',
        'password',
        'account_salary_id',
        'account_salary_name',
        'process_no',
        'status',
        'payment_done',
    ];
}
