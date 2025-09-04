<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Program;
use App\Models\Tweetcell;
use App\Models\Fatura;
use App\Models\TweetcellKontor;
use App\Models\TransferMoneyFirm;

use App\Models\Service;
use App\Utils\ProfitCalculationService;

use Illuminate\Support\Facades\Http;
class ApiAllServicesTotalController extends Controller
{   protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
       $appRecords =  Tweetcell::whereHas('tweetcellSection', function ($query) {
        $query->where('type', 2);
         })->count();
       $gameRecords = Tweetcell::whereHas('tweetcellSection', function ($query) {
        $query->where('type', 1);
         })->count();

        $ecardRecords = Tweetcell::whereHas('tweetcellSection', function ($query) {
        $query->where('type', 3);
         })->count();

       $serviceRecords = Service::whereHas('category', function ($query) {
        $query->where('type',1);
         })->count();

        $itsRecords = Service::whereHas('category', function ($query) {
        $query->where('type',2);
         })->count();
       $programRecords = Program::count();

       $faturaRecords = Fatura::count();
       $tweetcellKontorRecords=TweetcellKontor::count();
       $tweetcellRecords=Tweetcell::count();
       $transferMoneyFirmRecords = TransferMoneyFirm::count();
       return response()->json([
        'appRecords'=>$appRecords,
        'serviceRecords'=>$serviceRecords,
        'itsRecords'=>$itsRecords,
        'ecardRecords'=>$ecardRecords,
        'gameRecords'=>$gameRecords,
        'programRecords'=>$programRecords,
         'kontorRecords'=>$tweetcellKontorRecords,
         'transfer-mone-firmRecords'=>$transferMoneyFirmRecords,
         'faturaRecords'=>$faturaRecords,
    ]);
    }
    public function  myWallet()
    {
       $financials = $this->profitService->calculateUserFinancials( auth()->user()->id);
       return response()->json(['financials'=>$financials]);

    }

    function getServiceList()
    {
        $response = Http::withOptions([
            'verify' => false,
        ])->get('https://bayi.tweetpin.net/servis/pin_listesi.php?kod=5534060015&sifre=cayli831');
        dd( $response);
        // إعداد البيانات التي سترسل إلى API
        $data = [
            'action' => 'imeiservicelist', // العملية المطلوبة
            'username' => 'abdalkadr777352',      // اسم المستخدم
            'apiaccesskey' => 'QMG-U3B-MNS-J23-AAP-YKM-AG8-1SL',   // مفتاح الوصول
        ];

        // إرسال طلب POST إلى API
        $response = Http::asForm()->post('https://server.halabtech.com', $data);

        // التحقق من حالة الاستجابة
        if ($response->successful()) {
            // جلب البيانات إذا كانت الاستجابة ناجحة
            $services = $response->json();
            return $services;
        } else {
            // التعامل مع الأخطاء
            return [
                'error' => 'Failed to fetch services',
                'status' => $response->status(),
                'message' => $response->body(),
            ];
        }
    }


    /*<?php
$ch = curl_init("https://server.halabtech.com/api.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    echo 'Success: API Response Received';
}

curl_close($ch);
?>
*/



}
