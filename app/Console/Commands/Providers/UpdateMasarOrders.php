<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TweetcellOrder;
use App\Models\User;
use App\Models\Tweetcell;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Utils\ProfitCalculationService;
use App\Notifications\RecordAddedNotification;

class UpdateMasarOrders extends Command
{
    protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        parent::__construct();
        $this->profitService = $profitService;
    }

    protected $signature = 'app:update-masar-order-status';
    protected $description = 'تحديث حالة الطلبات لمزود Masar';

    public function handle()
    {
        Log::info('UpdateMasarOrderStatusNew-------------------');

        // تحميل جميع المستخدمين والخدمات مسبقًا لتقليل الاستعلامات
        $users = User::get()->keyBy('id');
        $services = Tweetcell::get()->keyBy('id');

        TweetcellOrder::where('status', 1)
            ->whereHas('tweetcell.tweetcellSection', function ($query) {
                $query->where('api_no', 2); // قسم Masar
            })
            ->chunk(100, function ($orders) use ($users, $services) {

                $baseUrl = 'https://api.masar-card.com/client/api/check';

                // جلب UUIDs للطلبات مع prefix ID_
                $orderIds = $orders->pluck('uuid')->map(fn($id) => "ID_$id")->toArray();
                $ordersParam = '[' . implode(',', $orderIds) . ']';

                $response = Http::withHeaders([
                    'api-token' => 'b74d86453d879b6fb9582b12dba2a9eb2519949b4f0fed7c',
                ])->get($baseUrl, ['orders' => $ordersParam]);

                $body = $response->json();

                if (($body['status'] ?? '') !== 'OK' || empty($body['data'])) {
                    Log::warning("MasarProvider: استجابة غير صالحة: " . json_encode($body));
                    return;
                }

                // دعم الحالة: الطلب واحد أو مصفوفة طلبات
                $results = is_array($body['data']) && isset($body['data'][0])
                    ? $body['data']
                    : [$body['data']];

                foreach ($results as $result) {
                    $orderIdInt = (int) str_replace('ID_', '', $result['order_id'] ?? '');
                    $order = $orders->firstWhere('uuid', $orderIdInt);
                    if (!$order) continue;

                    $user = $users->get($order->user_id);
                    $service = $services->get($order->tweetcell_id);
                    $status = strtolower($result['status'] ?? '');

                    // قراءة النص من replay_api
                    $replay = '';
                    if (!empty($result['replay_api'][0]['replay'][0])) {
                        $replay = $result['replay_api'][0]['replay'][0];
                    }

                    switch ($status) {
                        case 'accept':
                            $order->status = 2;
                            $order->reject_reason = $replay;
                            $order->save();

                            $this->profitService->calculateProfit($order, Tweetcell::class, $order->tweetcell_id);

                            $user->notify(new RecordAddedNotification([
                                'request_id' => $order->id,
                                'status' => 1,
                                'message' => 'تم تنفيذ طلب الشراء ' . ($service->name ?? ''),
                            ]));
                            break;

                        case 'wait':
                            // يمكن تجاهل أو تسجيل الانتظار
                            break;

                        case 'reject':
                            $order->status = 3;
                            $order->reject_reason = $replay;
                            $order->save();

                            $user->balance += $order->price;
                            $user->save();

                            $user->notify(new RecordAddedNotification([
                                'request_id' => $order->id,
                                'status' => 1,
                                'message' => 'تم رفض طلب الشراء ' . ($service->name ?? ''),
                            ]));
                            break;
                    }
                }
            });

        Log::info('UpdateMasarOrderStatusNew: تم الانتهاء من التحديث.');
    }
}
