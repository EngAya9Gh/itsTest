<?php  
namespace App\Services\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Setting;

class MasarProvider implements ProviderInterface
{
    public function sendOrder($order, $input)
    {
        $user = Auth::user();
        $setting = Setting::first();

        $url = "https://api.masar-card.com/client/api/newOrder/{$input['oyun_id']}/params";
        $query = [
            'qty' => $input['kupur'],
            'playerId' => $input['player_no'],
            'order_uuid' => $order->uuid,
        ];

        $response = Http::withHeaders([
            'api-token' => $setting->masar_api_token,
        ])->post($url, $query);

        $json = $response->json();

        if ($response->ok() && $json['status'] === 'OK') {
            $this->markOrderAsCompleted($order, $user);
            return "تم إرسال الطلب إلى Masar";
        } else {
            $this->rejectOrder($order, $json['message'] ?? 'فشل غير معروف');
            return "فشل الطلب من Masar";
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
