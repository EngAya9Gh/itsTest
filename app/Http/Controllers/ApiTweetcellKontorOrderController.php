<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TweetcellOrder;
use App\Models\TweetcellKontorOrder;
use App\Models\TweetcellKontorSection;
use App\Models\TweetcellKontor;
use App\Models\Vip;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Utils\ProfitCalculationService;
use App\Jobs\UpdateKontorPackeges;
class ApiTweetcellKontorOrderController extends Controller
{
    protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
  public function test($mobile)
  {
   // بدلاً من تحميل كل الطلبيات دفعة واحدة، استخدم chunk لتحميلهم تدريجياً
 /*$orders=   TweetcellKontorOrder::where('status', 1)->get();
        $settings = Setting::first();
        $username = $settings->tweetcell_user_name;
        $password = $settings->tweetcell_password;

     
        foreach ($orders as $order) {
       

            $data = [
                'phone' => $username,
                'password' => $password,
                'order_id' => $order->order_id,
            ];

            // تحسين التعامل مع الاستجابة
            $response = Http::get('https://1kanal.pro/b2c-api/market/getOrder', $data);
   
//$url ='https://1kanal.pro/b2c-api/market/getPackages?phone=05534060015&password=12344321&gsm=05368700606';
// $response = Http::get('https://1kanal.pro/b2c-api/market/newOrder?phone=05534060015&password=12344321&operator=turktelecom&code=1200&number=05350213000');
              //          https://1kanal.pro/b2c-api/market/newOrder?phone=05534060015&password=12344321&operator=turktelekom&code=10161&number=05350213000
 */ $url ='https://1kanal.pro/b2c-api/market/getPackages?phone=05534060015&password=12344321&operator=vodafone';

    $response = Http::get($url);
  dd($response->body());
        
   
  }
public function testttt($mobile)
{
  
    $service = TweetcellKontor::find(11);
 // $section = TweetcellKontorSection::find($service->section_id);
   $url = "https://1kanal.pro/b2c-api/user/getBalance?phone=05534060015&password=12344321";
   $url ='https://1kanal.pro/b2c-api/market/getPackages?phone=05534060015&password=12344321&operator=vodafone';
   
//$url ='https://1kanal.pro/b2c-api/market/getPackages?phone=05534060015&password=12344321&gsm=05368700606';
// $response = Http::get('https://1kanal.pro/b2c-api/market/newOrder?phone=05534060015&password=12344321&operator=turktelecom&code=1200&number=05350213000');
              //          https://1kanal.pro/b2c-api/market/newOrder?phone=05534060015&password=12344321&operator=turktelekom&code=10161&number=05350213000
 $response = Http::get($url);
    dd($response->body());
    //$response = Http::get( "https://tweetcell.com.tr/api_v1/b2c-api/market/newOrder?phone=05599999999&password=asd991199&operator=turktelekom&code=1200&number=05523332500");
 /* $response = Http::get('https://tweetcell.com.tr/api_v1/b2c-api/market/newOrder', [
    'phone' =>'05599999999',
    'password' =>'asd991199',
    'operator' =>$section->name,
    'code' => $service->section_id == 1 && $service->kupur ? $service->kupur : $service->code,
    'number' =>$mobile
]);*/
      
  /*    $user = Auth::user();
    $service = TweetcellKontor::find(893);
    $section = TweetcellKontorSection::find(2);


     

$response = Http::get('https://1kanal.pro/b2c-api/market/newOrder', [
    'phone' =>'05534060015',
    'password' =>'0015aa',
    'operator' =>$section->name,
    'code' =>$service->code,
    'number' =>$mobile,
]);
      
  // بناء المعلمات المطلوبة
$params = [
    'phone' => '05534060015',
    'password' => '0015aa',
    'operator' => $section->name,
    'code' => $service->code,
    'number' => $mobile,
];

// بناء الرابط النهائي باستخدام http_build_query
$finalUrl = 'https://1kanal.pro/b2c-api/market/newOrder?' . http_build_query($params);

// طباعة الرابط النهائي
dd($finalUrl); // يستخد
  
  */
 /*     $url = 'https://1kanal.pro/b2c-api/market/turkcell/getPackages';
    $params = [
        'phone' => '0553 406 00 15',
        'password' => '0015aa',
        'gsm' => $mobile,
    ];*/
 // $response = Http::get($url);
//$response = Http::get('https://1kanal.pro/b2c-api/market/turkcell/getPackages?phone=05534060015&password=0015aa&gsm=5352532479');

  
 // $response = Http::get('https://1kanal.pro/b2c-api/market/turkcell/getPackages?phone=05534060015&password=0015aa&gsm=5352532479');

  //    $response = //Http::get('https://1kanal.pro/b2c-api/market/turktelekom/getPackages?phone=05534060015&password=0015aa&gsm=5350213000');
 // $response = Http::get('https://1kanal.pro/b2c-api/market/vodafone/getPackages?phone=05534060015&password=0015aa&gsm=5438678722');

 $response = Http::get('https://tweetcell.com.tr/api_v1/b2c-api/market/newOrder?phone=05599999999&password=asd991199&operator=turktelecom&code=1200&number=5350213000');
  //$response = Http::get('https://1kanal.pro/b2c-api/market/getPackages?phone=05534060015&password=0015aa&operator=turkcell');
 // $response = Http::get('https://1kanal.pro/b2c-api/market/getOrder?phone=05534060015&password=0015aa&order_id=370121');

  dd($response->body());  // Vodafone  TurkCell  SelamTelekom
  //$data = $response->json();

// التحقق من وجود بيانات في الحقل 'packages' وحساب عدد العناصر
//$numberOfPackages = count($data['packages']); 

// عرض عدد العناصر
//dd($numberOfPackages);
}
  
  
  public function store(Request $request)
{      
    $input = $request->all(); 
    $user = Auth::user();
    $input['user_id'] = $user->id;
      if ($user->balance >=  $input['price']) { 
        $order = TweetcellKontorOrder::create($input);

        $result = $this->operation($order, $input);
        return response()->json(['message' => $result]);
      }
      else
        
        return response()->json(['message' => "فشل عملية الشراء : الرصيد غير كافي"]);
       
}

public function operation($order, $input)
{
    $user = Auth::user();
    $service = TweetcellKontor::find($order->tweetcell_kontor_id);
    $section = TweetcellKontorSection::find($service->section_id);
      $settings = Setting::first();
        $username = $settings->tweetcell_user_name;
        $password = $settings->tweetcell_password;
    if ($user) {
     
      $params = [
    'phone' => $username,
    'password' => $password,
    'operator' => $section->name,
     'code' => $service->code,
    
        'number' => $input['mobile']
];

$url = 'https://1kanal.pro/b2c-api/market/newOrder?' . http_build_query($params);
      $response = Http::get($url);
        $responseData = $response->json();

        // التحقق من وجود البيانات في الاستجابة
        if (isset($responseData['status']) && $responseData['status'] == "success") { 
            // الوصول إلى القيم المطلوبة
            $status = $responseData['status'];
            $orderData = $responseData['order'];
      
            // تحديث حالة الطلب
            $order->status = 1;
            $order->reject_reason = "";
            $order->admin_reject_reason =$url;
            $order->order_id = $orderData['id'];
            $order->save();
           $user->balance=   $user->balance- $order->price;
           $order->save();
            return "جاري معالجة الطلب...";
        } else {
            // إذا كانت الاستجابة تحتوي على رسالة خطأ
            $order->status = 3;
            $order->reject_reason = $url;
            $order->admin_reject_reason = $responseData ?? "لا توجد رسالة خطأ";
            $order->save();

            return "فشل عملية الشراء: " . ($responseData['message'] ?? $responseData);
        }
    } else {
        $order->status = 3;
        $order->reject_reason = "فشل عملية الاتصال";
        $order->save();
        return "فشل عملية الشراء";
    }
}

  public function updateStatus()
{
    dispatch(new UpdateKontorPackeges());
}

}
