<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\User;
use App\Utils\ProfitCalculationService;
use App\Notifications\RecordAddedNotification;

class ServiceOrderController extends Controller
{ protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
      public function index()
    {
    $currentUser = auth()->user();


// جلب كافة طلبات المستخدمين الذين لهم نفس الـ agent_id الخاص بالمستخدم المسجل
$serviceOrders = DB::table('service_orders')
    ->join('users as order_users', 'service_orders.user_id', '=', 'order_users.id') // ربط الطلب مع المستخدم صاحب الطلب
    ->join('services', 'service_orders.service_id', '=', 'services.id') // ربط الطلب مع الخدمة
    ->where('order_users.agent_id', '=', $currentUser->id) // التأكد من الـ agent_id
    ->select('service_orders.*', 'order_users.name as user_name', 'services.name as service_name') // جلب اسم صاحب الطلب من order_users
    ->orderBy('service_orders.created_at', 'desc') // ترتيب السجلات من الأحدث إلى الأقدم
    ->get();


     
        return view('backend.service.serviceOrders.index', compact('serviceOrders'));
}
       public function getOrder($type)
    {
    $currentUser = auth()->user();


// جلب كافة طلبات المستخدمين الذين لهم نفس الـ agent_id الخاص بالمستخدم المسجل
$serviceOrders = DB::table('service_orders')
    ->join('users as order_users', 'service_orders.user_id', '=', 'order_users.id') 
    ->join('services', 'service_orders.service_id', '=', 'services.id')  
    ->join('service_categories', 'services.section_id', '=', 'service_categories.id') 
    ->where('order_users.agent_id', '=', $currentUser->id) // التأكد من الـ agent_id
    ->where('service_categories.type', '=', $type) // التأكد من الـ agent_id
    ->select('service_orders.*', 'order_users.name as user_name', 'services.name as service_name') // جلب اسم صاحب الطلب من order_users
    ->orderBy('service_orders.created_at', 'desc') // ترتيب السجلات من الأحدث إلى الأقدم
    ->get();


     
        return view('backend.service.serviceOrders.index', compact('serviceOrders','type'));
}
   public function reject( $id,Request $request)
    {
        $order= ServiceOrder::findOrFail($id);
        $service=Service::find($order->service_id);
        $user=User::findOrFail($order->user_id);
        $order->status=3;
        $order->reject_reason=$request->reject_reason;
        $order->save();
        $user->balance=$user->balance+$order->price;
        $user->save();
       $agent = User::find($user->agent_id);
        $requestDetails = [ 
            'request_id' => $order->id,
            'status' => 1,
            'message' => ' تم رفض طلب شراء   ' . $service->name . ' من قبل  ' . $agent->name
        ];

        // إرسال الإشعار
        $user->notify(new RecordAddedNotification($requestDetails)); 

                return back()->with('message', 'تمت العملية  بنجاح');
     
    }
	public function accept( $id)
    {
        $order= ServiceOrder::findOrFail($id);
        $service=Service::find($order->service_id);
        $user=User::findOrFail($order->user_id);
        $order->status=2;
        $order->save();
        $this->profitService->calculateProfit($order, Service::class,$order->service_id);

        $agent = User::find($user->agent_id);
                $requestDetails = [ 
                    'request_id' => $order->id,
                     'status' => 1,
                     'message' => ' تم تفعيل طلب شراء   ' . $service->name . ' من قبل  ' . $agent->name
                ];

                // إرسال الإشعار
                $user->notify(new RecordAddedNotification($requestDetails)); 

               return back()->with('message', 'تمت العملية  بنجاح');
      }
 
    public function update(Request $request,  $id)
    {
        $gameOrder = ServiceOrder::findOrFail($id);
        $input = $request->all();
        $gameOrder->update($input);
        
        return back()->with('message', 'تم التعديل بنجاح');
    }
     public function destroy(string $id)
     {
        $service= ServiceOrder::findOrFail($id);
         
    // تحديد المسار الذي يتم تخزين الصورة فيه (افترض أن الصورة مخزنة في مجلد 'public/images/service/orders/')
    $imagePath = public_path('assets/images/service/orders/' . $service->kimlik);

    // التحقق إذا كانت الصورة موجودة ثم حذفها
    if (File::exists($imagePath)) {
        File::delete($imagePath);
    }
            
    $imagePath = public_path('assets/images/service/orders/' . $service->line_photo);

    // التحقق إذا كانت الصورة موجودة ثم حذفها
    if (File::exists($imagePath)) {
        File::delete($imagePath);
    }    

    // حذف السجل من جدول ServiceOrder
    $service->delete();

        return back()->with('message', 'تم الحذف  بنجاح');
    }

}
