<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceOrder;
use App\Models\Service;
use App\Models\Vip;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Utils\ProfitCalculationService;
use App\Notifications\RecordAddedNotification;

class ApiServiceOrderController extends Controller
{
    protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }

    public function store(Request $request)
    {
        // Validate inputs (IMEI will be enforced by parameter builder when needed)
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'price'      => 'required|numeric|min:0',
            'count'      => 'nullable|integer|min:0',
            'ime'        => 'nullable|string',
            'username'   => 'nullable|string',
            'email'      => 'nullable|email',
            'password'   => 'nullable|string',
            // optional server fields
            'MODELID'    => 'sometimes',
            'PROVIDERID' => 'sometimes',
            'MEP'        => 'sometimes',
            'PIN'        => 'sometimes',
            'KBH'        => 'sometimes',
            'PRD'        => 'sometimes',
            'TYPE'       => 'sometimes',
            'REFERENCE'  => 'sometimes',
            'LOCKS'      => 'sometimes',
            'SN'         => 'sometimes',
            'SecRO'      => 'sometimes',
        ]);

        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['message' => 'المستخدم غير مسجل دخول'], 401);
        }

        $service = Service::with('category')->findOrFail($validated['service_id']);

        // Pricing: minimum 1 if not provided or zero
        $count = (int)($validated['count'] ?? 0);
        if ($count <= 0) { $count = 1; }
        $totalPrice = ((float)$validated['price']) * $count;

        // Quick check before locking
        if ($authUser->balance < $totalPrice) {
            return response()->json(['message' => 'فشل عملية الشراء: الرصيد غير كافٍ'], 400);
        }

        $order = null;

        // Phase 1: lock balance and create order (short transaction)
        try {
            DB::beginTransaction();

            // Lock user row to avoid race conditions on balance
            $user = User::where('id', $authUser->id)->lockForUpdate()->firstOrFail();
            if ($user->balance < $totalPrice) {
                DB::rollBack();
                return response()->json(['message' => 'فشل عملية الشراء: الرصيد غير كافٍ'], 400);
            }

            // Create local order in pending state
            $orderInput = array_merge($validated, [
                'user_id' => $user->id,
                'status'  =>1,
            ]);
            $order = ServiceOrder::create($orderInput);

            // Deduct balance safely and verify persistence
            $user->balance = $user->balance - $totalPrice;
            if (!$user->save() || !$user->wasChanged('balance')) {
                throw new \RuntimeException('فشل حفظ رصيد المستخدم');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Phase1 failed: '.$e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء التحضير للطلب'], 500);
        }

        // Non-critical: notify agent (outside transaction)
        try {
            if ($authUser->agent_id) {
                $agent = User::find($authUser->agent_id);
                if ($agent) {
                    $serviceName = $service->name ?? '';
                    $agent->notify(new RecordAddedNotification([
                        'request_id' => $order->id,
                        'status'     => 1,
                        'message'    => ' طلب ' . $serviceName . ' من  ' . $authUser->name,
                    ]));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('RecordAddedNotification failed: '.$e->getMessage());
        }

        // Build Dhru parameters (XML or base64(JSON))
        $paramsMode  = strtolower((string)env('DHRU_PARAMS_MODE', 'xml')); // xml | jsonb64
        try {
            if ($paramsMode === 'xml') {
                $parameters = $this->buildDhruParametersXml($request, $order, $service);
            } else { // jsonb64
                $parameters = $this->buildDhruParametersJsonB64($request, $order, $service);
            }
         } catch (\Throwable $e) {
            // On building error: refund and reject
            $this->refundAndReject($authUser->id, $order, $totalPrice, 'خطأ في إنشاء الباراميترات: '.$e->getMessage());
            Log::error('DHRU parameters11 (mode='.$paramsMode.')', ['parameters' => $parameters]);

            return response()->json(['message' => 'فشل إنشاء باراميترات الطلب'], 422);
        }

        // Log parameters exactly as sent
         Log::info('DHRU parameters (mode='.$paramsMode.')', ['parameters' => $parameters]);
        if ($paramsMode === 'jsonb64' && is_string($parameters) && preg_match('~^[A-Za-z0-9+/=]+$~', $parameters)) {
            Log::info('DHRU parameters decoded (JSON)', [
                'decoded' => json_decode(base64_decode($parameters), true)
            ]);
        }

        // Provider request
        $providerUrl = config('app.dhru_provider_url') ?? env('DHRU_PROVIDER_URL');
        $username    = env('DHRU_USERNAME');
        $apiKey      = env('DHRU_API_KEY');

        try {
            $response = Http::asForm()->timeout(30)->post($providerUrl, [
                'username'      => $username,
                'apiaccesskey'  => $apiKey,
                'action'        => 'placeimeiorder',
                'requestformat' => 'JSON',
                'parameters'    => $parameters,
            ]);
        } catch (\Throwable $e) {
            // Transport-level failure: refund and reject
            $this->refundAndReject($authUser->id, $order, $totalPrice, 'فشل الإرسال إلى المزود: '.$e->getMessage());
            return response()->json(['message' => 'فشل الإرسال إلى المزود: '.$e->getMessage()], 500);
        }

        if (!$response->ok()) {
            $this->refundAndReject($authUser->id, $order, $totalPrice, 'HTTP error from provider: '.$response->status());
            return response()->json(['message' => 'فشل من المزود'], 502);
        }

        $payload = $response->json();
        $referenceId  = $this->extractReferenceId($payload);
        $errorMessage = $this->extractErrorMessage($payload);

        if ($referenceId) {
            // Phase 2 (success): update order as sent
            try {
                DB::transaction(function () use ($order, $referenceId) {
                    $order->refresh();
                    $order->provider_reference = (int)$referenceId;
                    $order->status = 1;
                    $order->save();
                });
            } catch (\Throwable $e) {
                // If updating order fails, try to refund to avoid funds being stuck
                $this->refundAndReject($order->user_id, $order, $totalPrice, 'فشل تحديث الطلب بعد نجاح المزود: '.$e->getMessage());
                return response()->json(['message' => 'فشل حفظ نتائج المزود'], 500);
            }

            return response()->json([
                'message'      => 'تم تسجيل طلبك وإرساله للمزود بنجاح',
                'reference_id' => (int)$referenceId,
                'order_id'     => $order->id,
            ], 201);
        }

        // Provider returned failure: refund and reject
        $this->refundAndReject($authUser->id, $order, $totalPrice, $errorMessage ?: 'فشل من المزود');
        return response()->json(['message' => $errorMessage ?: 'فشل من المزود'], 422);
    }

    private function refundAndReject(int $userId, ServiceOrder $order, float $amount, string $reason): void
    {
        try {
            DB::transaction(function () use ($userId, $order, $amount, $reason) {
                // Refund with lock
                $user = User::where('id', $userId)->lockForUpdate()->firstOrFail();
                $user->balance = $user->balance + $amount;
                $user->save();

                // Mark order rejected
                $order->refresh();
                $order->status = 3;
                $order->reject_reason = $reason;
                $order->save();
            });
        } catch (\Throwable $e) {
            Log::error('Refund/Reject failed: '.$e->getMessage());
        }
    }

    /**
     * Build Dhru parameters as base64(JSON) depending on service category rules.
     */
    private function buildDhruParametersJsonB64(Request $request, ServiceOrder $order, Service $service): string
    {
        $categoryName = strtoupper(trim(optional($service->category)->name ?? ''));
        $externalId   = $service->external_id; // required
        if (!$externalId) {
            throw new \InvalidArgumentException('الخدمة لا تحتوي external_id');
        }

        // Build optional CUSTOMFIELD payload from user inputs
        $customFieldArray = [];
        if ($request->filled('username')) { $customFieldArray['Username'] = (string)$request->input('username'); }
        if ($request->filled('email'))    { $customFieldArray['Email']    = (string)$request->input('email'); }
        if ($request->filled('password')) { $customFieldArray['Password'] = (string)$request->input('password'); }
        if ($request->filled('ime'))      { $customFieldArray['Mobile']   = (string)$request->input('ime'); }
        $customField = empty($customFieldArray) ? null : base64_encode(json_encode($customFieldArray, JSON_UNESCAPED_UNICODE));

        $params = [];

        if ($categoryName === 'IMEI') {
            if (!$request->filled('ime')) {
                throw new \InvalidArgumentException('IMEI مطلوب لهذه الخدمة');
            }
            $params['IMEI'] = (string)$request->input('ime');
            $params['ID']   = (int)$externalId;
            if ($customField) { $params['CUSTOMFIELD'] = $customField; }
        } elseif ($categoryName === 'SERVER') {
            $params['ID'] = (int)$externalId;
            $count = (int)$request->input('count', 0);
            if ($count > 0) { $params['QNT'] = $count; }
            if ($request->filled('ime')) { $params['SN'] = (string)$request->input('ime'); }
            foreach (['MODELID','PROVIDERID','MEP','PIN','KBH','PRD','TYPE','REFERENCE','LOCKS','SecRO'] as $key) {
                if ($request->filled($key)) { $params[$key] = $request->input($key); }
            }
            if ($customField) { $params['CUSTOMFIELD'] = $customField; }
        } else {
            $params['ID'] = (int)$externalId;
            if ($request->filled('ime')) { $params['IMEI'] = (string)$request->input('ime'); }
            if ($customField) { $params['CUSTOMFIELD'] = $customField; }
        }

        return base64_encode(json_encode($params, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Build Dhru parameters as XML string (without XML header) depending on service category rules.
     */
    private function buildDhruParametersXml(Request $request, ServiceOrder $order, Service $service): string
    {
        $categoryName = strtoupper(trim(optional($service->category)->name ?? ''));
        $externalId   = $service->external_id; // required
        if (!$externalId) {
            throw new \InvalidArgumentException('الخدمة لا تحتوي external_id');
        }

        // Build CUSTOMFIELD base64(JSON) from user inputs
        $cf = [];
        if ($request->filled('username')) { $cf['Username'] = (string)$request->input('username'); }
        if ($request->filled('email'))    { $cf['Email']    = (string)$request->input('email'); }
        if ($request->filled('password')) { $cf['Password'] = (string)$request->input('password'); }
        if ($request->filled('ime'))      { $cf['Mobile']   = (string)$request->input('ime'); }
        //
        //  Log::info('customfield : '.cf);
        $customFieldB64 = empty($cf) ? null : base64_encode(json_encode($cf, JSON_UNESCAPED_UNICODE));

        $xml = new \SimpleXMLElement('<PARAMETERS/>');

        if ($categoryName === 'IMEI') {
            if (!$request->filled('ime')) {
                throw new \InvalidArgumentException('IMEI مطلوب لهذه الخدمة');
            }
            $xml->addChild('IMEI', (string)$request->input('ime'));
            $xml->addChild('ID', (string)(int)$externalId);
            if ($customFieldB64) { $xml->addChild('CUSTOMFIELD', $customFieldB64); }
        } elseif ($categoryName === 'SERVER') {
            $xml->addChild('ID', (string)(int)$externalId);
            $count = (int)$request->input('count', 0);
            if ($count > 0) { $xml->addChild('QNT', (string)$count); }
            if ($request->filled('ime')) { $xml->addChild('SN', (string)$request->input('ime')); }
            foreach (['MODELID','PROVIDERID','MEP','PIN','KBH','PRD','TYPE','REFERENCE','LOCKS','SecRO'] as $k) {
                if ($request->filled($k)) { $xml->addChild($k, (string)$request->input($k)); }
            }
            if ($customFieldB64) { $xml->addChild('CUSTOMFIELD', $customFieldB64); }
        } else {
            $xml->addChild('ID', (string)(int)$externalId);
            if ($request->filled('ime')) { $xml->addChild('IMEI', (string)$request->input('ime')); }
            if ($customFieldB64) { $xml->addChild('CUSTOMFIELD', $customFieldB64); }
        }

        $raw = $xml->asXML();
        return preg_replace('/^<\\?xml.*?\\?>\\s*/', '', (string)$raw);
    }

    private function extractReferenceId(?array $payload): ?string
    {
        if (!$payload) { return null; }
        if (isset($payload['SUCCESS'][0]['REFERENCEID'])) {
            return (string)$payload['SUCCESS'][0]['REFERENCEID'];
        }
        if (isset($payload['RESULT']['SUCCESS'][0]['REFERENCEID'])) {
            return (string)$payload['RESULT']['SUCCESS'][0]['REFERENCEID'];
        }
        if (isset($payload['REFERENCEID'])) {
            return (string)$payload['REFERENCEID'];
        }
        return null;
    }

    private function extractErrorMessage(?array $payload): ?string
    {
        if (!$payload) { return null; }
        if (isset($payload['ERROR'][0]['MESSAGE'])) {
            return (string)$payload['ERROR'][0]['MESSAGE'];
        }
        if (isset($payload['RESULT']['ERROR'][0]['MESSAGE'])) {
            return (string)$payload['RESULT']['ERROR'][0]['MESSAGE'];
        }
        if (isset($payload['MESSAGE'])) {
            return (string)$payload['MESSAGE'];
        }
        return null;
    }
}
