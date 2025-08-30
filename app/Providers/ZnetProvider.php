<?php namespace App\Services\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Setting;

class ZnetProvider implements ProviderInterface
{
    public function sendOrder($order, $input)
    {
        $user = Auth::user();
        $setting = Setting::first();

        $data = [
            'kod' => $setting->znet_user_name,
            'sifre' => $setting->znet_password,
            'oyun' => $input['oyun_id'],
            'kupur' => $input['kupur'],
            'referans' => $order->id + 1000,
            'musteri_tel' => $user->mobile,
            'oyuncu_bilgi' => $input['player_no'],
        ];

        $response = Http::get('https://bayi.tweetpin.net/servis/pin_ekle.php', $data);

        if (Str::startsWith($response, 'OK')) {
            $this->markOrderAsCompleted($order, $user);
            return "جاري معالجة الطلب (Znet)";
        } else {
            $this->rejectOrder($order, $response->body());
            return "خطأ في Znet: تأكد من البيانات";
        }
    }

    protected function markOrderAsCompleted($order, $user)
    {
        $user->balance -= $order->price;
        $user->save();
        $order->status = 1;
        $order->save();
    }

    protected function rejectOrder($order, $reason)
    {
        $order->status = 1;
        $order->reject_reason = $reason;
        $order->save();
        $order->delete();
    }
}
