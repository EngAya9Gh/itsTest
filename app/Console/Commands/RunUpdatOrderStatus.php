<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TweetcellOrder;
use App\Models\User;
use App\Models\Tweetcell;
use App\Models\Setting;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Utils\ProfitCalculationService;
use App\Notifications\RecordAddedNotification;
    
    
class RunUpdatOrderStatus extends Command
{
  protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        parent::__construct();  // استدعاء المُنشئ للأب

        $this->profitService = $profitService;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-updat-order-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
 public function handle()
{
    Log::info('UpdateOrderStatusNew------------------- ');

    // بدلاً من تحميل كل الطلبيات دفعة واحدة، استخدم chunk لتحميلهم تدريجياً
    TweetcellOrder::where('status', 1)->chunk(100, function ($orders) {
        $settings = Setting::first();
        $username = $settings->znet_user_name;
        $password = $settings->znet_password;

        // بدلاً من استعلامات find لكل مستخدم، استخدم whereIn لتحميل المستخدمين دفعة واحدة
        $userIds = $orders->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        foreach ($orders as $order) {
             $service=Tweetcell::find($order->tweetcell_id);

            $user = $users->get($order->user_id);
            if (!$user) {
                continue; // الانتقال إلى الطلب التالي
            }

            $data = [
                'kod' => $username,
                'sifre' => $password,
                'tahsilat_api_islem_id' => $order->id + 1000,
            ];

            // تحسين التعامل مع الاستجابة
            $response = Http::get('https://bayi.tweetpin.net/servis/pin_kontrol.php', $data);

            if (Str::startsWith($response, 'OK')) {
                $responseBody = $response->body();
                $res = explode("|", $responseBody);

                if (isset($res[1]) && $res[1] == 3) {
                    $order->status = 3;
                    $order->reject_reason = $res[3] ?: "التأكد من المعرف من فضلك";
                    $user->balance += $order->price;
                    $user->save();
                    $order->save();
                     $agent = User::find($user->agent_id);
                    $requestDetails = [ 
                      'request_id' => $order->id,
                      'status' => 1,
                      'message' => ' تم رفض طلب شراء   ' . $service->name
                    ];

                   $user->notify(new RecordAddedNotification($requestDetails)); 

                }

                if (isset($res[1]) && $res[1] == 2) {
                    $order->status = 2;
                    $order->reject_reason = $res[2];
                    $order->save();  
                    $this->profitService->calculateProfit($order, Tweetcell::class, $order->tweetcell_id);
                    $agent = User::find($user->agent_id);
                    $requestDetails = [ 
                      'request_id' => $order->id,
                      'status' => 1,
                      'message' => ' تم تفعيل طلب شراء   ' . $service->name
                    ];

                   $user->notify(new RecordAddedNotification($requestDetails)); 

                }
            } else {
                $order->reject_reason = "";
                $order->save();
            }
        }
    });
}

}
