<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TweetcellKontorOrder;
use App\Models\User;
use App\Models\TweetcellKontor;
use App\Models\Setting;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Utils\ProfitCalculationService;
use App\Notifications\RecordAddedNotification;

class RunUpdatOrderStatusKontor extends Command
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
    protected $signature = 'app:run-updat-order-status-kontor';

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
    Log::info('UpdateOrderStatusKonotr------------------- ');

    // بدلاً من تحميل كل الطلبيات دفعة واحدة، استخدم chunk لتحميلهم تدريجياً
    TweetcellKontorOrder::where('status', 1)->chunk(100, function ($orders) {
        $settings = Setting::first();
        $username = $settings->tweetcell_user_name;
        $password = $settings->tweetcell_password;

        // بدلاً من استعلامات find لكل مستخدم، استخدم whereIn لتحميل المستخدمين دفعة واحدة
        $userIds = $orders->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        foreach ($orders as $order) {
          $service=TweetcellKontor::find($order->tweetcell_kontor_id);
            $user = $users->get($order->user_id);
            if (!$user) {
                continue; // الانتقال إلى الطلب التالي
            }

            $data = [
                'phone' => $username,
                'password' => $password,
                'order_id' => $order->order_id,
            ];

            // تحسين التعامل مع الاستجابة
            $response = Http::get('https://1kanal.pro/b2c-api/market/getOrder', $data);
              $data = json_decode($response, true);
              $status = $data['status'];
                    if ($status== 'success') {
               $orderStatus = $data['order']['status']; 
                if (isset($orderStatus) && $orderStatus == "cancel") {
                    $order->status = 3;
                    $order->reject_reason = $data['order']['note'];
                    $user->balance += $order->price;
                    $user->save();
                    $order->save();
                   $requestDetails = [ 
                                  'request_id' => $order->id,
                                  'status' => 1,
                                  'message' => ' تم رفض طلب شراء   ' . $service->name
                                ];
                   $user->notify(new RecordAddedNotification($requestDetails));
                }

                if (isset($orderStatus) && $orderStatus == "done") {
                    $order->status = 2;
                    $order->reject_reason =  $data['order']['note'];
                    $order->save();  
                    $this->profitService->calculateProfit($order, TweetcellKontor::class, $order->tweetcell_kontor_id);
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
