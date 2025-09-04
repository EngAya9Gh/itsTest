<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateServiceOrdersStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update service orders status from Dhru provider';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting service orders status update...');

        try {
            // جلب الطلبات التي في حالة انتظار ولها provider_reference
            $pendingOrders = ServiceOrder::where('status', 1)
                ->whereNotNull('provider_reference')
                ->where('provider_reference', '!=', '')
                ->get();

            if ($pendingOrders->isEmpty()) {
                $this->info('No pending orders with provider reference found.');
                return;
            }

            $this->info("Found {$pendingOrders->count()} pending orders to check.");

            // تجميع الطلبات في مجموعات (bulk requests)
            $orderChunks = $pendingOrders->chunk(50); // معالجة 50 طلب في كل مرة

            foreach ($orderChunks as $chunk) {
                $this->processOrderChunk($chunk);
            }

            $this->info('Service orders status update completed successfully.');

        } catch (\Exception $e) {
            $this->error('Error updating service orders status: ' . $e->getMessage());
            Log::error('UpdateServiceOrdersStatus Command Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * معالجة مجموعة من الطلبات
     */
    private function processOrderChunk($orders)
    {
        // إنشاء parameters للـ API
        $parameters = [];
        $orderMapping = []; // لربط req مع order_id

        $reqIndex = 1;
        foreach ($orders as $order) {
            $reqKey = "req{$reqIndex}";
            $parameters[$reqKey] = ["ID" => $order->provider_reference];
            $orderMapping[$reqKey] = $order->id;
            $reqIndex++;
        }

        // طباعة البارامترز قبل التشفير في اللوغ
        Log::info('Dhru API Parameters before encoding:', ['parameters' => $parameters]);
        $this->info('Parameters: ' . json_encode($parameters, JSON_UNESCAPED_UNICODE));

        // تشفير البارامترز
        $encodedParameters = base64_encode(json_encode($parameters, JSON_UNESCAPED_UNICODE));

        // طباعة البارامترز المشفرة في اللوغ والكونسول
        Log::info('Dhru API Encoded Parameters:', ['encoded_parameters' => $encodedParameters]);
        $this->info('Encoded Parameters: ' . $encodedParameters);

        // إرسال الطلب للـ API
        $response = $this->callDhruAPI($encodedParameters);

        if ($response) {
            $this->updateOrdersFromResponse($response, $orderMapping);
        }
    }

    /**
     * استدعاء API الخاص بـ Dhru
     */
    private function callDhruAPI($encodedParameters)
    {
        try {
            $response = Http::asForm()->post(env('DHRU_PROVIDER_URL'), [
                'username' => env('DHRU_USERNAME'),
                'apiaccesskey' => env('DHRU_API_KEY'),
                'action' => 'getimeiorderbulk',
                'parameters' => $encodedParameters,
                'requestformat' => 'JSON'
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Dhru API Response:', $responseData);
                return $responseData;
            } else {
                Log::error('Dhru API Error: ' . $response->status() . ' - ' . $response->body());
                $this->error('API request failed: ' . $response->status());
                return null;
            }

        } catch (\Exception $e) {
            Log::error('Dhru API Exception: ' . $e->getMessage());
            $this->error('API request exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * تحديث الطلبات بناءً على استجابة الـ API
     */
    private function updateOrdersFromResponse($response, $orderMapping)
    {
        foreach ($response as $reqKey => $reqData) {
            if (!isset($orderMapping[$reqKey])) {
                continue;
            }

            $orderId = $orderMapping[$reqKey];
            $order = ServiceOrder::find($orderId);

            if (!$order) {
                continue;
            }

            // معالجة الاستجابة
            if (isset($reqData['SUCCESS']) && is_array($reqData['SUCCESS'])) {
                foreach ($reqData['SUCCESS'] as $successItem) {
                    $this->processOrderStatus($order, $successItem);
                }
            }
        }
    }

    /**
     * معالجة حالة الطلب الواحد
     */
    private function processOrderStatus($order, $statusData)
    {
        $dhruStatus = $statusData['STATUS'] ?? null;
        $imei = $statusData['IMEI'] ?? '';
        $code = $statusData['CODE'] ?? '';
        $comments = $statusData['COMMENTS'] ?? '';

        Log::info("Processing order {$order->id} with Dhru status: {$dhruStatus}");

        // تحويل حالة Dhru إلى حالة النظام
        $newStatus = $this->mapDhruStatusToSystemStatus($dhruStatus);

        if ($newStatus !== null && $newStatus !== $order->status) {
            DB::beginTransaction();
            try {
                $oldStatus = $order->status;
                $order->status = $newStatus;

                // إضافة معلومات إضافية في reject_reason
                $resultInfo = [];
                if (!empty($imei)) {
                    $resultInfo[] = "IMEI: {$imei}";
                }
                if (!empty($code)) {
                    $resultInfo[] = "Code: {$code}";
                }
                if (!empty($comments)) {
                    $resultInfo[] = "Comments: {$comments}";
                }

                if (!empty($resultInfo)) {
                    $order->reject_reason = implode(' | ', $resultInfo);
                }

                $order->save();

                // إذا تم رفض الطلب، أرجع الرصيد للمستخدم
                if ($newStatus == 3) { // مرفوض
                    $this->refundUserBalance($order);
                }

                DB::commit();

                $this->info("Order {$order->id} status updated from {$oldStatus} to {$newStatus}");
                Log::info("Order {$order->id} status updated", [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'dhru_status' => $dhruStatus,
                    'reject_reason' => $order->reject_reason,
                    'imei' => $imei,
                    'code' => $code,
                    'comments' => $comments
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error updating order {$order->id}: " . $e->getMessage());
                $this->error("Error updating order {$order->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * تحويل حالة Dhru إلى حالة النظام
     * Dhru: 0-New, 1-InProcess, 3-Reject(Refund), 4-Available(Success)
     * System: 1-انتظار, 2-تم بنجاح, 3-تم الرفض
     */
    private function mapDhruStatusToSystemStatus($dhruStatus)
    {
        switch ($dhruStatus) {
            case '0': // New
            case '1': // InProcess
                return 1; // انتظار
            case '4': // Available(Success)
                return 2; // تم بنجاح
            case '3': // Reject(Refund)
                return 3; // تم الرفض
            default:
                return null; // لا تغيير
        }
    }

    /**
     * إرجاع الرصيد للمستخدم في حالة الرفض
     */
    private function refundUserBalance($order)
    {
        try {
            $user = User::find($order->user_id);
            if ($user) {
                $user->balance += $order->price;
                $user->save();

                Log::info("Refunded {$order->price} to user {$user->id} for rejected order {$order->id}");
                $this->info("Refunded {$order->price} to user {$user->name} for rejected order {$order->id}");
            }
        } catch (\Exception $e) {
            Log::error("Error refunding balance for order {$order->id}: " . $e->getMessage());
            $this->error("Error refunding balance for order {$order->id}: " . $e->getMessage());
        }
    }
}
