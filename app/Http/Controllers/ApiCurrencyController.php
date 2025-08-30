<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Currency;

class ApiCurrencyController extends Controller

{public function getExchangeRates($baseCode)
{
    $baseCurrency = Currency::where('is_base', 1)->first();
    $baseCode=$baseCurrency->code;
    if (!$baseCurrency || $baseCurrency->rate == 0) {
        throw new \Exception("العملة غير موجودة أو سعرها غير صالح.");
    }

    $currencies = Currency::all();

    $rates = [];

    foreach ($currencies as $currency) {
        if ($currency->rate == 0) {
            continue; // لتجنب القسمة على صفر
        }

        if ($currency->code != $baseCode) {
        
            // سعر صرف العملة الأساسية مقابل العملة الأخرى
            // = 1 / سعر العملة الأخرى (المخزن بالنسبة لعملة الأساس)
            $rateAgainstBase = round(1 / $currency->rate,3);
            $rates[] = [
            'code' => $currency->name,
            'name' => $currency->name,
            'rate' => $rateAgainstBase
        ];
        }

        
    }

    return response()->json([
        'base' => $baseCode,
        'rates' => $rates,
    ]);
}



}
