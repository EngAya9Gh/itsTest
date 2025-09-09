<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceCategories;
use App\Models\Service;
use App\Models\Vip;
use App\Utils\ProfitCalculationService;

class ApiServiceCategoryController extends Controller
{
      protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
       $categories=DB::table('service_categories')->select('*')->where('type',1)->where('status',1)->orderBy('id', 'desc')->paginate(500);

       foreach ($categories as $app) {
         $app->image_url = asset('assets/images/service/' . $app->image);  // إنشاء رابط للصورة
     }

       return response()->json(['categories'=> $categories ]);
    }

      public function getIts()
    {
       $categories=DB::table('service_categories')->select('*')->where('type',2)->where('status',1)->orderBy('id', 'desc')->paginate(500);

       foreach ($categories as $app) {
         $app->image_url = asset('assets/images/service/' . $app->image);  // إنشاء رابط للصورة
     }

       return response()->json(['categories'=> $categories ]);
    }

    public function getServices (Request $request, string $section_id)
    {
       $cat = ServiceCategories::where('id',$section_id)->where('status',1)->first();

       if (!$cat) {
           return response()->json(['error' => 'الصنف غير موجود'], 404);
       }

       // إعداد الاستعلام مع البحث
       $query = $cat->services()->where('status', 1);

       // إضافة البحث إذا تم توفير كلمة بحث
       if ($request->has('search') && !empty($request->search)) {
           $searchTerm = $request->search;
           $query->where(function($q) use ($searchTerm) {
               $q->where('name', 'LIKE', "%{$searchTerm}%")
                 ->orWhere('note', 'LIKE', "%{$searchTerm}%");
           });
       }

       // تطبيق pagination مع تحديد الحد الأقصى
       $perPage = $request->get('per_page', 12); // 12 عنصر افتراضياً

       // تحديد الحد الأدنى والأقصى لعدد العناصر في كل صفحة
       $minPerPage = 6;
       $maxPerPage = 50;

       // التأكد من أن القيمة ضمن النطاق المسموح
       if ($perPage < $minPerPage) {
           $perPage = $minPerPage;
       } elseif ($perPage > $maxPerPage) {
           $perPage = $maxPerPage;
       }

       $services = $query->paginate($perPage);

       foreach ($services as $app) {
         $app->image_url = asset('assets/images/service/' . $app->image);  // إنشاء رابط للصورة

        $user=Auth::user();
         if($user && $user->role!=4)
          $app->sale_price =$this->profitService->getSalePrice($app->price);
         else
           $app->sale_price =0;
        $app->price = $this->profitService->getPrice($app);    // إنشاء رابط
     }

       return response()->json([
           'services' => $services,
           'category' => [
               'id' => $cat->id,
               'name' => $cat->name,
               'description' => $cat->description
           ]
       ]);
    }


}
