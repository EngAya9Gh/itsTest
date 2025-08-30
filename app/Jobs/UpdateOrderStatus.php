<?php

namespace App\Jobs;
use App\Models\TweetcellOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UpdateOrderStatus implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

   // protected $userId;

    public function __construct()
    {
       // $this->userId = $userId;
    }


public function handle()
{
    Log::info('Starting job for user: ');

    

    $orders = TweetcellOrder::where('status', 1)->get();
    Log::info('Orders found: ' . $orders->count());
    $username = "5534060015";
    $password ="ucgen636";

    foreach ($orders as $order) {
        $user = User::find($order->user_id);
    if (!$user) {
        Log::error('User not found for ID: ');
        return;
    }
        Log::info('Processing order ID: ' . $order->id);

        $data = [
            'kod' => $username,
            'sifre' => $password,
            'tahsilat_api_islem_id' => $order->id+1000,
        ];

        $response = Http::get('https://bayi.tweetpin.net/servis/pin_kontrol.php', $data);

        Log::info('Response received: ' . $response->body());
        if (Str::startsWith($response, 'OK'))
     {
            $responseBody = $response->body();
            $res = explode("|", $responseBody);

            if (isset($res[1]) && $res[1] == 3) {
                $order->status = 3;
                $order->reject_reason = $res[3] ?: "التأكد من المعرف من فضلك";
                $user->balance += $order->price;
                $user->save();
                $order->save();
                Log::info('Order updated to status 3');
            }

            if (isset($res[1]) && $res[1] == 2) {
                $order->status = 2;
                $order->reject_reason = $res[2] ;
                $order->save();
                Log::info('Order updated to status 2');
            }
        } else {
            $order->reject_reason = "";
            $order->save();
            Log::info('Failed response from API');
        }
    }
}
}
