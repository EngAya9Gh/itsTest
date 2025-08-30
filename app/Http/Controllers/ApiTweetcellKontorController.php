<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\TweetcellKontor;
use App\Models\TweetcellKontorSection;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Http;
use App\Utils\ProfitCalculationService;  
 

class ApiTweetcellKontorController extends Controller
{  protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }

    public function index()
    { 
        $dataCommunications=DB::table('tweetcell_kontors')->select('*')->orderBy('id', 'desc')->paginate(500);
        foreach ($dataCommunications as $app) {
            $app->image_url = asset('assets/images/tweetcellKontorSections/' . $app->image);  // إنشاء رابط للصورة
           
        }
       
        return response()->json(['dataCommunications'=>$dataCommunications ]);
    }

    public function show($id)
    {
       $dataCommunication = TweetcellKontor::findOrFail($id); 
       
       return response()->json(['dataCommunication'=>$dataCommunication]);
    }



public function getPackages(Request $request)
{
  $section = TweetcellKontorSection::find($request->section_id);
  
$baseUrl = 'https://1kanal.pro/b2c-api/market/getPackages';
 $settings = Setting::first();
        $username = $settings->tweetcell_user_name;
        $password = $settings->tweetcell_password;

  $params = [
    'phone' => $username,
    'password' =>$password,
    'operator' => $section->title,
];

   $finalUrl = $baseUrl . '?' . http_build_query($params);




    try {
        // استدعاء الـ API
      $response = Http::get($finalUrl);
     
        // التأكد من نجاح الاستجابة
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['packages'])) {
          
                 // استخراج الحزم من الاستجابة
            $packages = $response->json()['packages'];

                    // معالجة الحزم فقط إذا كانت الحزم موجودة
                    $updateData = [];
                    foreach ($packages as $package) {

                        $updateData[] = [
                            'section_id' => $section->id,
                            'code' => $package['code'],
                            'name' => $package['name'],
                            'basic_price' => $package['price'],
                            'price' =>round(( $package['price'] + ($package['price'] * $section->increase_percentage / 100)),4),
                        ];
                    }

                    // تنفيذ عملية التحديث الجماعي
                    foreach ($updateData as $dataItem) {
                      if($section->id!=1)
                        TweetcellKontor::updateOrCreate(
                            ['section_id' => $dataItem['section_id'], 'code' => $dataItem['code']],
                            $dataItem
                        );
                      else
                      {
                         /*  TweetcellKontor::updateOrCreate(
                            ['section_id' => $dataItem['section_id'],'name' => $dataItem['name']],
                            $dataItem
                        );*/
                        
                       // نحذف كل الفراغات ونحول الاسم إلى حروف صغيرة
                        $normalizedName = strtolower(preg_replace('/\s+/', '', $dataItem['name']));

                        // نحذف الفراغات ونحول لحروف صغيرة أثناء الاستعلام من الجدول
                        $kontor = TweetcellKontor::where('section_id', $dataItem['section_id'])
                            ->whereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$normalizedName])
                            ->first();

                        if ($kontor) {
                            $kontor->update($dataItem);
                        } else {
                            TweetcellKontor::create($dataItem);
                        }

                      }
                    }
            
              
             $codes = collect($data['packages'])->pluck('code')->toArray();
                // حذف العناصر المكررة من المصفوفة
 
              $codes = array_unique($codes);
    
                // التأكد من أن المصفوفة تحتوي على أكواد غير فارغة
                $codes = array_filter($codes);
    
                // إعادة الفهرسة للمصفوفة بعد إزالة العناصر المكررة والفارغة
                $codes = array_values($codes);
     
              $financials = $this->profitService->calculateUserFinancials(auth()->user()->id);

              $packagesQuery = TweetcellKontor::where(function($query) use ($codes) {
                  $query->whereIn('kupur', $codes)
                        ->orWhereIn('code', $codes);
              });

              if ($financials['debts'] != 0) {
                  $packagesQuery->where('is_firsat', 0);
              }

              $packages = $packagesQuery->get();
              
              foreach( $packages as $app)
              {       $app->sale_price = $this->profitService->getSalePrice($app->price);    // إنشاء رابط 

                      $app->price = $this->profitService->getPrice($app);    // إنشاء رابط 

              }
      
                return response()->json(["packages" => $packages,'error' =>""]);
            } else {
                return response()->json(["packages" => [],'error' => 'لايوجد باقات لهذا الرقم']);
            }
        } else {
            return response()->json(["packages" => [],'error' =>$response->json()['message']]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'حدثت مشكلة أثناء الاتصال بالخادم. الرجاء المحاولة لاحقًا.',
            'details' => $e->getMessage(), // يمكنك إزالة هذه إذا كنت لا تريد إظهار تفاصيل الاستثناء للمستخدم
        ], 500);
    }
}
   
 
 
 
 
public function getPackagesMobile(Request $request)
{
  $section = TweetcellKontorSection::find($request->section_id);
  
$baseUrl = 'https://1kanal.pro/b2c-api/market/' .strtolower(trim($section->title)). '/getPackages';
 $settings = Setting::first();
        $username = $settings->tweetcell_user_name;
        $password = $settings->tweetcell_password;

  $params = [
    'phone' => $username,
    'password' =>$password,
    'gsm' => $request->mobile,
];

   $finalUrl = $baseUrl . '?' . http_build_query($params);




    try {
        // استدعاء الـ API
      $response = Http::get($finalUrl);
     
        // التأكد من نجاح الاستجابة
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['packages'])) {
            
              $codes = explode(',', $data['packages']);
    
                // حذف العناصر المكررة من المصفوفة
 
              $codes = array_unique($codes);
    
                // التأكد من أن المصفوفة تحتوي على أكواد غير فارغة
                $codes = array_filter($codes);
    
                // إعادة الفهرسة للمصفوفة بعد إزالة العناصر المكررة والفارغة
                $codes = array_values($codes);
     
              $financials = $this->profitService->calculateUserFinancials(auth()->user()->id);

              $packagesQuery = TweetcellKontor::where(function($query) use ($codes) {
                  $query->whereIn('kupur', $codes)
                        ->orWhereIn('code', $codes);
              });

              if ($financials['debts'] != 0) {
                  $packagesQuery->where('is_firsat', 0);
              }

              $packages = $packagesQuery->get();
              
              foreach( $packages as $app)
              {       $app->sale_price = $this->profitService->getSalePrice($app->price);    // إنشاء رابط 

                      $app->price = $this->profitService->getPrice($app);    // إنشاء رابط 
                
              }
      
                return response()->json(["packages" => $packages,'error' =>""]);
            } else {
                return response()->json(["packages" => [],'error' => 'لايوجد باقات لهذا الرقم']);
            }
        } else {
            return response()->json(["packages" => [],'error' =>$response->json()['message']]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'حدثت مشكلة أثناء الاتصال بالخادم. الرجاء المحاولة لاحقًا.',
            'details' =>$finalUrl, // يمكنك إزالة هذه إذا كنت لا تريد إظهار تفاصيل الاستثناء للمستخدم
        ], 500);
    }
}
  
  
  
 /*  public function getPackages(Request $request)

    
   {
       
       $url = 'https://tweetcell.com.tr/api_v1/b2c-api/market/getPackages?phone=05599999999&password=asd991199&oprator=turktelekom';
    
       $response = Http::get($url);
dd($response->body());

       if ($response->successful()) {
           $data = $response->json();
       // $games = $response->json()['result'];

        // حفظ البيانات في ملف JSON
        $filePath = storage_path('app/KONTOR_data.json');
        File::put($filePath, json_encode($data));
         
         
           $packages = TweetcellKontor::whereRaw("REPLACE(type, ' ', '') = ?", [str_replace(' ', '', $request->type)])
                           ->get();

         
           // التأكد من وجود الحقل "packages"
           if (isset($data['packages'])) {
               // تحويل الأكواد إلى مصفوفة
               $codes = array_column($data['packages'], 'code');
             //dd($codes);
               // البحث في قاعدة البيانات باستخدام الأكواد
            $packages = TweetcellKontor::where(function($query) use ($codes) {
                            $query->whereIn('kupur', $codes)
                                  ->orWhereIn('code', $codes);
                        })
                        ->where('type', intval($request->type))  // شرط type الأساسي
                        ->get();             
       //   dd($packages);
               // إرجاع البيانات (أو يمكن إرجاع نتائج معينة فقط)
               return response()->json(["packeges"=>$packages]);
           } else {
               return response()->json(['error' => 'No packages found in the response.']);
           }
       } else {
           return response()->json(['error' => 'Failed to fetch data from API.'], 500);
       }
}

*/
}

