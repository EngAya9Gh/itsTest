<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TweetcellOrder;
use App\Models\Tweetcell;
use App\Models\TweetcellSection;
use App\Models\Vip;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Utils\ProfitCalculationService;
use App\Jobs\UpdateOrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Notifications\RecordAddedNotification;
use Carbon\Carbon;
use App\Providers\ProviderManager;

class ApiTweetcellOrderController extends Controller
{
   
    protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }

    public function store(Request $request)
{
        $input = $request->all();
        $user = Auth::user();
     
         $input['device_info']="";
        // إضافة user_id إلى المدخلات
        $input['user_id'] = $user->id;
    
        // تحقق من الرصيد والقيام بالعملية إذا كان الرصيد كافي
        if ($user->balance >= $input['price'] ) {
            $order = TweetcellOrder::create($input); // إنشاء الطلب
          
        $service = Tweetcell::find($order->tweetcell_id);  // جلب الخدمة بناءً على tweetcell_id
        $section=TweetcellSection::where('id',$service->section_id)->first();
         // خريطة المزودات بناءً على رقم API
        $providersMap = [
            1 => 'znet',
            2 => 'masar',
            3 => 'another', // مثال لمزود إضافي مستقبلي
        ];
        
        // تحديد المزود بناءً على api_no
        $source = $providersMap[$section->api_no] ?? 'znet'; // الافتراضي znet
        $requestDetails = [ 
            'request_id' => $order->id,
            'status' => 1,
            'message' => ' طلب ' . $service->name . ' من  ' . $user->name
        ];
         // عند إضافة طلب جديد
        $user = User::find($user->agent_id);  // أو تحديد المستخدم المستهدف للإشعار
        
        // إرسال الإشعار
        $user->notify(new RecordAddedNotification($requestDetails)); 
              
            
        $result = $this->operation($order, $input,$source); // القيام بعملية أخرى إذا لزم الأمر
            return response()->json(['message' => $result]); // إرجاع النتيجة
        } else {
            return response()->json(['message' => "فشل عملية الشراء: الرصيد غير كافي"]); // إذا كان الرصيد غير كافي
        }
}



    public function operation($order, $input,$source)
    {
        $providerName = $source; // اسم المزود، مثل: masar أو znet
        try {
            $provider = ProviderManager::make($providerName);
            return $provider->sendOrder($order, $input);
        } catch (\Exception $e) {
            return "فشل: " . $e->getMessage();
        }
    }

 


public function updateStatus($id)
{
    // إرسال الـ Job لتحديث الحالة في الخلفية
    dispatch(new UpdateOrderStatus($id));
}
public function myRequests($id)
{
 // الحصول على التواريخ من المعلمات (startDate, endDate)
        $startDate = request()->query('startDate');  // جلب startDate من معلمات GET
        $endDate = request()->query('endDate');  // جلب endDate من معلمات GET

        // دالة لتصفية الطلبات حسب التواريخ
        $filterByDate = function ($query) use ($startDate, $endDate) {
            if ($startDate) {
                $query->whereDate('orders.created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('orders.created_at', '<=', $endDate);
            }
        };

        // قائمة الطلبات
        $orders = collect();
 
       $services = DB::table('service_categories')
            ->join('services', 'service_categories.id', '=', 'services.section_id')
            ->join('service_orders as orders', 'services.id', '=', 'orders.service_id')
            ->where('service_categories.type', 1)
            ->where('orders.user_id', $id);
     

        // تطبيق تصفية التواريخ
        $filterByDate($services);

        $services = $services->select(
                'services.id',
                'services.name',
                'orders.price',
                 'orders.ime',
                 'orders.email',
                'orders.status',
                'orders.created_at',
                'orders.reject_reason',
                'orders.uuid',
                'services.image_url',
                DB::raw("'خدمات سرفر' as type")
            )->get();
          // جلب طلبات الخدمات

      $its = DB::table('service_categories')
            ->join('services', 'service_categories.id', '=', 'services.section_id')
            ->join('service_orders as orders', 'services.id', '=', 'orders.service_id')
            ->where('service_categories.type', 2)
            ->where('orders.user_id', $id);
      
        // تطبيق تصفية التواريخ
        $filterByDate($its);

        $its = $its->select(
                'services.id',
                'services.name',
                'orders.price',
                 'orders.ime',
                 'orders.email',
                'orders.status',
                'orders.created_at',
                'orders.reject_reason',
                'orders.uuid',
                'services.image_url',
                DB::raw("' ITS خدمات' as type")
            )->get();

 $faturas = DB::table('fatura_sections')
            ->join('faturas', 'fatura_sections.id', '=', 'faturas.section_id')
            ->join('fatura_orders as orders', 'faturas.id', '=', 'orders.fatura_id')
            ->where('orders.user_id', $id);
      
        // تطبيق تصفية التواريخ
        $filterByDate($faturas);

        $faturas = $faturas->select(
                'faturas.id',
                'faturas.name',
                'orders.price',
                'orders.fatura_no',
                'orders.status',
                'orders.created_at',
                'orders.reject_reason',
                'orders.uuid',
                'faturas.image_url',
                DB::raw("'فواتير' as type")
            )->get();  
  
  // جلب طلبات التطبيقات
        $apps = DB::table('tweetcell_sections')
            ->join('tweetcells', 'tweetcell_sections.id', '=', 'tweetcells.section_id')
            ->join('tweetcell_orders as orders', 'tweetcells.id', '=', 'orders.tweetcell_id')
            ->where('tweetcell_sections.type', 2)
            ->where('orders.user_id', $id);

        // تطبيق تصفية التواريخ
        $filterByDate($apps);

        $apps = $apps->select(
                'tweetcells.id',
                'tweetcells.name',
                'orders.price',
                'orders.status',
                'orders.player_no',
                'orders.reject_reason',
                'orders.created_at',
                'tweetcells.image_url',
                'orders.uuid',
                DB::raw("'التطبيقات' as type")
            )->get();
  
    $games = DB::table('tweetcell_sections')
            ->join('tweetcells', 'tweetcell_sections.id', '=', 'tweetcells.section_id')
            ->join('tweetcell_orders as orders', 'tweetcells.id', '=', 'orders.tweetcell_id')
            ->where('tweetcell_sections.type', 1)
            ->where('orders.user_id', $id);

        // تطبيق تصفية التواريخ
        $filterByDate($games);

        $games = $games->select(
                'tweetcells.id',
                'tweetcells.name',
                'orders.price',
                'orders.status',
                'orders.player_no',          
                'orders.reject_reason',
                'orders.created_at',
                'tweetcells.image_url',
                'orders.uuid',
                DB::raw("'الالعاب' as type")
            )->get();
  
        // جلب طلبات التطبيقات
        $ecards = DB::table('tweetcell_sections')
            ->join('tweetcells', 'tweetcell_sections.id', '=', 'tweetcells.section_id')
            ->join('tweetcell_orders as orders', 'tweetcells.id', '=', 'orders.tweetcell_id')
            ->where('tweetcell_sections.type',3)
            ->where('orders.user_id', $id);

        // تطبيق تصفية التواريخ
        $filterByDate($ecards);

        $ecards = $ecards->select(
                'tweetcells.id',
                'tweetcells.name',
                'orders.price',
                'orders.status',
                'orders.player_no',
                'orders.reject_reason',
                'orders.created_at',
                'tweetcells.image_url',
                'orders.uuid',
                DB::raw("'البطاقات الرقمية' as type")
            )->get();

        // جلب طلبات الكونتور
        $kontors = DB::table('tweetcell_kontor_sections')
            ->join('tweetcell_kontors', 'tweetcell_kontor_sections.id', '=', 'tweetcell_kontors.section_id')
            ->join('tweetcell_kontor_orders as orders', 'tweetcell_kontors.id', '=', 'orders.tweetcell_kontor_id')
            ->where('orders.user_id', $id);

        // تطبيق تصفية التواريخ
        $filterByDate($kontors);

        $kontors = $kontors->select(
                'tweetcell_kontors.id',
                'tweetcell_kontors.name',
                'orders.price',
                'orders.status',
                'orders.mobile',
                'orders.reject_reason',
                'orders.created_at',
                'tweetcell_kontors.image_url',
                'orders.uuid',
                DB::raw("'الكونتور ' as type")
            )->get();

  
  
        // دمج جميع الطلبات
        $orders = $orders->merge($services)
            ->merge($games)
            ->merge($its)
            ->merge($apps)
            ->merge($ecards)
            ->merge($kontors);
        $orders = $orders->map(function ($order) {
            $order->created_at = Carbon::parse($order->created_at)->format('Y-m-d H:i:s');
            return $order;
        });
    return response()->json(["orders" => $orders]);
}
  
public function myRequestsByType($id,$type)
{
 // الحصول على التواريخ من المعلمات (startDate, endDate)
        $startDate = request()->query('startDate');  // جلب startDate من معلمات GET
        $endDate = request()->query('endDate');  // جلب endDate من معلمات GET

        // دالة لتصفية الطلبات حسب التواريخ
        $filterByDate = function ($query) use ($startDate, $endDate) {
            if ($startDate) {
                $query->whereDate('orders.created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('orders.created_at', '<=', $endDate);
            }
        };

        // قائمة الطلبات
        $orders = collect();
  if($type=='service')
  { $services = DB::table('service_categories')
            ->join('services', 'service_categories.id', '=', 'services.section_id')
            ->join('service_orders as orders', 'services.id', '=', 'orders.service_id')
            ->where('service_categories.type', 1)
            ->where('orders.user_id', $id);
     

        // تطبيق تصفية التواريخ
        $filterByDate($services);

        $services = $services->select(
                'services.id',
                'services.name',
                'orders.price',
                 'orders.ime',
                 'orders.email',
                'orders.status',
                'orders.created_at',
                'orders.reject_reason',
                'orders.uuid',
                'services.image_url',
                DB::raw("'خدمات سرفر' as type")
            )->get();
    $orders = $orders->merge($services);
  }
   if($type=='its')
   {$its = DB::table('service_categories')
            ->join('services', 'service_categories.id', '=', 'services.section_id')
            ->join('service_orders as orders', 'services.id', '=', 'orders.service_id')
            ->where('service_categories.type', 2)
            ->where('orders.user_id', $id);
      
        // تطبيق تصفية التواريخ
        $filterByDate($its);

        $its = $its->select(
                'services.id',
                'services.name',
                'orders.price',
                 'orders.ime',
                 'orders.email',
                'orders.status',
                'orders.created_at',
                'orders.reject_reason',
                'orders.uuid',
                'services.image_url',
                DB::raw("' ITS خدمات' as type")
            )->get();
    $orders = $orders->merge($its);
     }

   if($type=='fatura')
  { $faturas = DB::table('fatura_sections')
            ->join('faturas', 'fatura_sections.id', '=', 'faturas.section_id')
            ->join('fatura_orders as orders', 'faturas.id', '=', 'orders.fatura_id')
            ->where('orders.user_id', $id);
      
        // تطبيق تصفية التواريخ
        $filterByDate($faturas);

        $faturas = $faturas->select(
                'faturas.id',
                'faturas.name',
                'orders.price',
                'orders.fatura_no',
                'orders.status',
                'orders.created_at',
                'orders.reject_reason',
                'orders.uuid',
                'faturas.image_url',
                DB::raw("'فواتير' as type")
            )->get();  
  
       $orders = $orders->merge($faturas);
       
  }
 if($type=='app')
 {$apps = DB::table('tweetcell_sections')
            ->join('tweetcells', 'tweetcell_sections.id', '=', 'tweetcells.section_id')
            ->join('tweetcell_orders as orders', 'tweetcells.id', '=', 'orders.tweetcell_id')
            ->where('tweetcell_sections.type', 2)
            ->where('orders.user_id', $id);

        // تطبيق تصفية التواريخ
        $filterByDate($apps);

        $apps = $apps->select(
                'tweetcells.id',
                'tweetcells.name',
                'orders.price',
                'orders.status',
                'orders.player_no',
                'orders.reject_reason',
                'orders.created_at',
                'tweetcells.image_url',
                'orders.uuid',
                DB::raw("'التطبيقات' as type")
            )->get();
  
  $orders = $orders->merge($apps);
       
 }
  if($type=='game')
  { $games = DB::table('tweetcell_sections')
            ->join('tweetcells', 'tweetcell_sections.id', '=', 'tweetcells.section_id')
            ->join('tweetcell_orders as orders', 'tweetcells.id', '=', 'orders.tweetcell_id')
            ->where('tweetcell_sections.type', 1)
            ->where('orders.user_id', $id);

        // تطبيق تصفية التواريخ
        $filterByDate($games);

        $games = $games->select(
                'tweetcells.id',
                'tweetcells.name',
                'orders.price',
                'orders.status',
                'orders.player_no',          
                'orders.reject_reason',
                'orders.created_at',
                'tweetcells.image_url',
                'orders.uuid',
                DB::raw("'الالعاب' as type")
            )->get();
   
    $orders = $orders->merge($games);
      
  }
  
 if($type=='ecard')
 {$ecards = DB::table('tweetcell_sections')
            ->join('tweetcells', 'tweetcell_sections.id', '=', 'tweetcells.section_id')
            ->join('tweetcell_orders as orders', 'tweetcells.id', '=', 'orders.tweetcell_id')
            ->where('tweetcell_sections.type',3)
            ->where('orders.user_id', $id);

        // تطبيق تصفية التواريخ
        $filterByDate($ecards);

        $ecards = $ecards->select(
                'tweetcells.id',
                'tweetcells.name',
                'orders.price',
                'orders.status',
                'orders.player_no',
                'orders.reject_reason',
                'orders.created_at',
                'tweetcells.image_url',
                'orders.uuid',
                DB::raw("'البطاقات الرقمية' as type")
            )->get();

      $orders = $orders->merge($ecards);

 }
 if($type=='kontor')
 {        $kontors = DB::table('tweetcell_kontor_sections')
            ->join('tweetcell_kontors', 'tweetcell_kontor_sections.id', '=', 'tweetcell_kontors.section_id')
            ->join('tweetcell_kontor_orders as orders', 'tweetcell_kontors.id', '=', 'orders.tweetcell_kontor_id')
            ->where('orders.user_id', $id);

        // تطبيق تصفية التواريخ
        $filterByDate($kontors);

        $kontors = $kontors->select(
                'tweetcell_kontors.id',
                'tweetcell_kontors.name',
                'orders.price',
                'orders.status',
                'orders.mobile',
                'orders.reject_reason',
                'orders.created_at',
                'tweetcell_kontors.image_url',
                'orders.uuid',
                DB::raw("'الكونتور ' as type")
            )->get();
      $orders = $orders->merge($kontors);

}
  
        $orders = $orders->map(function ($order) {
            $order->created_at = Carbon::parse($order->created_at)->format('Y-m-d H:i:s');
            return $order;
        });
    return response()->json(["orders" => $orders]);
}
  
     
     
  public function myRequestUuid($uuid)  
{
    // البحث في جدول tweetcell_orders

  $tweetcellOrder = DB::table('tweetcell_sections')
    ->join('tweetcells', 'tweetcell_sections.id', '=', 'tweetcells.section_id')
    ->join('tweetcell_orders as orders', 'tweetcells.id', '=', 'orders.tweetcell_id')
    ->where('orders.player_no', $uuid)
    ->select(
        'tweetcells.id',
        'tweetcells.name',
        'orders.price',
        'orders.reject_reason',
        'orders.count',
         'orders.status',
        'orders.created_at',
        'orders.uuid',
    )
    ->first();

    // إذا تم العثور على السجل في tweetcell_orders
    if ($tweetcellOrder) {
        return response()->json([
            'order' => $tweetcellOrder
        ]);
    }

  
  $tweetcellKontorOrder = DB::table('tweetcell_kontor_sections')
    ->join('tweetcell_kontors', 'tweetcell_kontor_sections.id', '=', 'tweetcell_kontors.section_id')
    ->join('tweetcell_kontor_orders as orders', 'tweetcell_kontors.id', '=', 'orders.tweetcell_kontor_id')
    ->where('orders.mobile', $uuid)
    ->select(
        'tweetcell_kontors.id',
        'tweetcell_kontors.name',
        'orders.price',
        'orders.reject_reason',
        'orders.status',
        'orders.created_at',
        'orders.uuid',
    )
    ->first();

  
  
  
  
  
  
    // إذا تم العثور على السجل في tweetcell_orders
    if ($tweetcellKontorOrder) {
        return response()->json([
            'order' => $tweetcellKontorOrder
        ]);
    }

  
  
   $serviceOrder = DB::table('services')
        ->join('service_orders as orders', 'services.id', '=', 'orders.service_id')
        ->where('orders.ime', $uuid)
        ->select(
            'services.id',
            'services.name',
            'orders.price',
            'orders.reject_reason',
            'orders.username',
            'orders.password',
            'orders.email',
            'orders.count',
            'orders.note',
            'orders.status',
            'orders.created_at',
             'orders.uuid',
            'services.image_url',
            DB::raw("'Service' as type"),)->first();

    // إذا تم العثور على السجل في service_orders
    if ($serviceOrder) {
        return response()->json([
            'order' => $serviceOrder
        ]);
    }
  
  
   $faturaOrder = DB::table('faturas')
        ->join('fatura_orders as orders', 'faturas.id', '=', 'orders.fatura_id')
        ->where('orders.fatura_no', $uuid)
        ->select(
            'faturas.id',
            'faturas.name',
            'orders.price',
            'orders.reject_reason',
            'orders.note',
            'orders.status',
            'orders.created_at',
             'orders.uuid',
            'services.image_url',
            DB::raw("'Fatura' as type"),)->first();

    // إذا تم العثور على السجل في service_orders
    if ($serviceOrder) {
        return response()->json([
            'order' => $faturaOrder
        ]);
    }

    // إذا لم يتم العثور على أي سجل في كلا الجدولين
    return response()->json(['message' => 'Order not found'], 404);
}
}

