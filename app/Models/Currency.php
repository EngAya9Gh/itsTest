<?php
// app/Models/Currency.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'rate', 'is_base'];

    public static function getBaseCurrency()
    {
        return static::where('is_base', true)->first();
    }

    public function isBase()
    {
        return $this->is_base;
    }
}
