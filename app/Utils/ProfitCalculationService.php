<?php

namespace App\Utils;

use App\Models\Vip;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Profit;
use App\Models\Currency;
class ProfitCalculationService
  {

  public function getRateAtTime($currency)
  {
      $baseCurrency = Currency::where('is_base', true)->first();
      $targetCurrency = Currency::where('name', $currency)->orWhere('code', $currency)->first();

      if (!$baseCurrency) {
          throw new \Exception("لم يتم تحديد العملة الأساسية.");
      }

      if (!$targetCurrency) {
          throw new \Exception("العملة المطلوبة غير موجودة.");
      }

      if ($targetCurrency->rate <= 0) {
          throw new \Exception("سعر صرف غير صالح للعملة: {$currency}");
      }

      // إذا كانت العملة المطلوبة هي الليرة التركية، استخدم المعدل المحدد
      if (strtoupper($currency) === 'TRY' || strtoupper($targetCurrency->code) === 'TRY') {
          return 0.0242145;
      }

      // السعر هو: قيمة العملة الأساسية / قيمة العملة الهدف
      $rateAtTime = $baseCurrency->rate / $targetCurrency->rate;

      return round($rateAtTime, 6);
  }

  public function convertToBasicPrice($price, $currency)
  {
      $baseCurrency = Currency::where('is_base', true)->first();
      $orderCurrency = Currency::where('name', $currency)->first();

      if (!$baseCurrency) {
          throw new \Exception("لم يتم تحديد العملة الأساسية.");
      }

      if (!$orderCurrency) {
          // العملة غير معروفة، نعيد السعر كما هو
          return round($price, 2);
      }

      if ($orderCurrency->rate <= 0) {
          throw new \Exception("سعر صرف غير صالح للعملة: {$currency}");
      }

      // إذا كانت العملة هي الليرة التركية، استخدم المعدل المحدد
      if (strtoupper($currency) === 'TRY' || strtoupper($orderCurrency->code) === 'TRY') {
          $convertedAmount = $price * 0.0242145;
      } else {
          $convertedAmount = $price * ($baseCurrency->rate / $orderCurrency->rate);
      }

      return round($convertedAmount, 2);
  }

  /**
   * إضافة رسوم التحويل (20 سنت للدولار الأمريكي)
   */
  public function addConversionFee($amount, $targetCurrency)
  {
      // إذا كانت العملة المستهدفة هي الدولار الأمريكي، أضف 20 سنت
      if (strtoupper($targetCurrency) === 'USD') {
          return $amount + 0.20;
      }

      return $amount;
  }

  public function calculateProfit($order, $modelType, $serviceId)
{
    $user = User::find($order->user_id);
    $service = $modelType::findOrFail($serviceId);

    $count = $order->count ?? 1;
    $profitDifference = ($service->price - $service->basic_price) * $count;

    // العملة الأساسية
    $baseCurrency = Currency::where('is_base', true)->first();

    if (!$baseCurrency) {
        throw new \Exception("لم يتم تحديد العملة الأساسية.");
    }

    $lastProfit = 0;

    // توزيع الربح
    $currentUser = $user;

    // إذا كان المستخدم من المستوى 2 أو 3 أو 4
    if (in_array($user->role, [2, 3, 4])) {
        if ($user->role == 2 || $user->role == 3) {
            $vip = Vip::find($user->vip_id);
            if ($vip) {
                $lastProfit = $profitDifference * ($vip->commession_percent / 100);
            }
        }

        while ($currentUser && $currentUser->agent_id) {
            $agent = User::find($currentUser->agent_id);

            if (!$agent) break;

            $vip = Vip::find($agent->vip_id);
            if (!$vip) break;

            // حساب الربح للوكيل
            $agentProfit = $profitDifference * ($vip->commession_percent / 100) - $lastProfit;

            // تحويل الربح إلى العملة المفضلة للوكيل (للتخزين المرجعي فقط)
            $agentCurrency = $agent->preferred_currency_id
                ? Currency::find($agent->preferred_currency_id)
                : $baseCurrency;

            $rateAtTime = ($agentCurrency && $agentCurrency->id != $baseCurrency->id)
                ? ($baseCurrency->rate / $agentCurrency->rate)
                : 1;

            // تخزين الربح
            $order->profits()->create([
                'user_id' => $agent->id,
                'profit_amount' => round($agentProfit, 2),             // بالعملة الأساسية
                'profitable_id' => $order->id,
                'currency_id' => $baseCurrency->id,                    // العملة الأساسية
                'user_currency_id' => $agentCurrency->id,              // عملة الوكيل
                'user_rate_at_time' => $rateAtTime                     // سعر الصرف المستخدم
            ]);

            // تحديث رصيد الوكيل
         $agent->balance_profit += round($agentProfit / $rateAtTime, 2);
            $agent->save();

            // الانتقال للوكيل الأعلى
            $lastProfit += $agentProfit;
            $currentUser = $agent;
        }
    }

    return true;
}
   public function getPrice($service)
{
    $user = auth()->user();
    if (!$user) {
        return $service->price; // بدون تحويل، غير مسجّل
    }

    $profitDifference = $service->price - $service->basic_price;

    // حساب السعر بحسب الدور أو الـ VIP
    if ($user->role == 2 || $user->role == 3) {
        $vip = Vip::find($user->vip_id);
        $price = round(($service->price - ($profitDifference * $vip->commession_percent / 100)), 4);
    } elseif ($user->role == 4) {
        $price = $service->price;
    } else {
        $price = $service->basic_price;
    }

    // تحويل العملة إذا كان للمستخدم عملة مفضلة
    if ($user->preferred_currency_id) {
        $preferredCurrency = \App\Models\Currency::find($user->preferred_currency_id);
        $baseCurrency = \App\Models\Currency::getBaseCurrency(); // مثل TRY

        if ($preferredCurrency && $preferredCurrency->id !== $baseCurrency->id) {
            // تحويل من العملة الأساسية إلى عملة المستخدم المفضلة
            if (strtoupper($preferredCurrency->code) === 'TRY') {
                // استخدام المعدل المحدد للليرة التركية
                $convertedPrice = round($price * 0.0242145, 4);
            } else {
                $convertedPrice = round($price * $preferredCurrency->rate, 4);
            }

            // إضافة رسوم التحويل إذا كانت العملة المفضلة هي الدولار
            $convertedPrice = $this->addConversionFee($convertedPrice, $preferredCurrency->code);

            return $convertedPrice;
        }
    }

    return $price; // إرجاع بالسعر الأصلي إن لم يكن هناك تحويل
}
 public function getSalePrice($price)
{
    $user = auth()->user();
    if (!$user || !$user->preferred_currency_id) {
        return $price;
    }

    $preferredCurrency = \App\Models\Currency::find($user->preferred_currency_id);
    $baseCurrency = \App\Models\Currency::getBaseCurrency(); // مثل TRY

    if ($preferredCurrency && $preferredCurrency->id !== $baseCurrency->id) {
        if (strtoupper($preferredCurrency->code) === 'TRY') {
            // استخدام المعدل المحدد للليرة التركية
            $convertedPrice = round($price * 0.0242145, 4);
        } else {
            $convertedPrice = round($price * $preferredCurrency->rate, 4);
        }

        // إضافة رسوم التحويل إذا كانت العملة المفضلة هي الدولار
        $convertedPrice = $this->addConversionFee($convertedPrice, $preferredCurrency->code);

        return $convertedPrice;
    }

    return $price;
}public function calculateUserFinancials($userId)
{
    $user = User::findOrFail($userId);

    $balance = $user->balance;
    $profit = $user->balance_profit;

    $incoming = Transaction::where('to_user_id', $userId)->sum('amount');
    $outgoing = Transaction::where('from_user_id', $userId)->sum('amount');
    $debts = Transaction::where('to_user_id', $userId)
                        ->where('payment_done', 0)
                        ->sum('amount');

    // الأرباح بالعملة الأساسية
    $profitTotalsBase = Profit::where('user_id', $userId)->sum('profit_amount');

    // الأرباح المحوّلة إلى العملة المفضلة وقت الإنشاء
    $profits = Profit::where('user_id', $userId)->get();

    $convertedProfit = 0;
    foreach ($profits as $profitEntry) {
        // إذا تم تخزين معدل التحويل وقت الربح
        if ($profitEntry->user_rate_at_time > 0) {
            $convertedProfit += $profitEntry->profit_amount / $profitEntry->user_rate_at_time;
        } else {
            // fallback - في حال لم يتم تخزين السعر
            $convertedProfit += $profitEntry->profit_amount;
        }
    }

    // معرفة رمز العملة المفضلة
    $currencySymbol = '';
    if ($user->preferred_currency_id) {
        $preferredCurrency = Currency::find($user->preferred_currency_id);
        if ($preferredCurrency) {
            $currencySymbol = $preferredCurrency->symbol;
        }
    }

    return [
        'incoming' => $incoming,
        'outgoing' => $outgoing,
        'profitTotals_base' => $profitTotalsBase,
        'profitTotals' => round($convertedProfit, 2),
        'profitCurrencySymbol' => $currencySymbol,
        'debts' => $debts,
        'balance' => $balance,
        'profit' => $profit,
    ];
}

/*
    public function calculateUserFinancials($userId)
    {
        $balance=User::find($userId)->balance;
         $profit=User::find($userId)->balance_profit;
        // الوارد
        $incoming = Transaction::where('to_user_id', $userId)
                                ->sum('amount');

        // الصادر
        $outgoing = Transaction::where('from_user_id', $userId)
                                ->sum('amount');

        // الأرباح
        $profitTotals = Profit::where('user_id', $userId)->sum('profit_amount');

        // الديون
        $debts = Transaction::where('to_user_id', $userId)
                            ->where('payment_done', 0)
                            ->sum('amount');

        return [
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'profitTotals'  => $profitTotals,
            'debts'    => $debts,
            'balance'=>$balance,
            'profit'=>$profit,
        ];
    }
  */

    public function getDebts($userId)
    {
        $balance=User::find($userId)->balance;
        $profit=User::find($userId)->balance_profit;

        $debts = Transaction::where('to_user_id', $userId)
                            ->where('payment_done', 0)
                            ->sum('amount');

        return $debts ;
    }


}
