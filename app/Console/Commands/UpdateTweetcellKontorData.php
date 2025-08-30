<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TweetcellKontor;
use App\Models\Setting;
use App\Models\TweetcellKontorSection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UpdateTweetcellKontorData extends Command
{
    protected $signature = 'tweetcellKontor:update';
    protected $description = 'Fetch and update Tweetcell Kontor data from API';
    public function __construct()
    {
        // استدعاء المُنشئ الأساسي (parent constructor)
        parent::__construct();
    }
  public function handle(): void
{
    \Log::info('Start processing UpdateKontorPackeges job'); // بداية التنفيذ

    // الحصول على جميع الأقسام
    $sections = TweetcellKontorSection::all();//turkcell
    $this->info('Command kontor started !');
    foreach ($sections as $section) {
        $this->info('Processing section: '.$section->name); // إضافة رسالة للقسم الجاري معالجته

          $setting=Setting::all()[0];
       $username =$setting->tweetcell_user_name;
        $password = $setting->tweetcell_password;
        
        // إعداد البيانات للطلب
        $data = [
            'phone' => trim($username),
            'password' => trim($password),
            'operator' => trim($section->title),
        ];
        
        // رابط الـ API  'https://tweetcell.com.tr/api_v1/b2c-api/market/getPackages?phone=05599999999&password=asd991199&operator=vodafone' 
        $url = 'https://tweetcell.com.tr/api_v1/b2c-api/market/getPackages';

        // إرسال الطلب
        $response = Http::get($url,$data);

        // التحقق من نجاح الاستجابة
        if ($response->successful()) {
            \Log::info('Successfully fetched packages for section: ' . $section->id); // نجاح استجابة الـ API
          $this->info($response->body());

            // استخراج الحزم من الاستجابة
            $packages = $response->json()['packages'];

            if (!empty($packages)) {
                \Log::info('Found ' . count($packages) . ' packages for section: ' . $section->id);
                 
                if($section->id==2)  //turkcell
                {
                    // معالجة الحزم فقط إذا كانت الحزم موجودة
                    $updateData = [];
                    foreach ($packages as $package) {
                        \Log::info('Processing package code: ' . $package['code']); // طباعة كود الحزمة

                        // إضافة الحزم إلى البيانات المعدة للتحديث
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
                        TweetcellKontor::updateOrCreate(
                            ['section_id' => $dataItem['section_id'], 'code' => $dataItem['code']],
                            $dataItem
                        );
                    }
                }
              else {
                    $codes = array_column($packages, 'code');
                     if($section->id==1)
                    $existingPackages = TweetcellKontor::whereIn('kupur', $codes)->get();
                     else{
                    $existingPackages = TweetcellKontor::where(function($query) use ($codes) {
                        $query->whereIn('code', $codes)
                              ->orWhereIn('kupur', $codes);
                    })->get(); 
                     }
                 foreach ($existingPackages as $package) {
                    $packageData = collect($packages)->first(function ($item) use ($package) {
                        return $item['code'] == $package->kupur || $item['code'] == $package->code;
                    });                        if ($packageData) {
                            $section = TweetcellKontorSection::find($package->section_id);

                            $package->basic_price = $packageData['price'];
                            $package->name = $packageData['name'];
                            $package->price = $packageData['price'] + ($packageData['price'] * $section->increase_percentage / 100);
                            $package->save();
                            \Log::info('Updated package with code: ' . $package->kupur);
                        }
                    }
                }
            } else {
                \Log::error('No packages found for section: ' . $section->id);
            }
        } else {
            \Log::error('Failed to fetch packages for section: ' . $section->id . ' - Response status: ' . $response->status());
        }
    }

    \Log::info('Finished processing UpdateKontorPackeges job');
}

}





/*
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TweetcellKontor;
use App\Models\TweetcellKontorSection;
use Illuminate\Support\Facades\File;
use Http;
class UpdateTweetcellKontorData extends Command
{
   

    protected $signature = 'tweetcellKontor:update';
    protected $description = 'Fetch and update Tweetcell Kontor data from API';

    public function handle()
    { 
        $url = 'https://1kanal.pro/b2c-api/market/getPackages?phone=05534060015&password=0015aa&oprator=turktelekom';
    
       $response = Http::get($url);


       if ($response->successful()) {
           $data = $response->json();
 
           if (isset($data['packages']))
           {
               // تحويل الأكواد إلى مصفوفة
               $codes = array_column($data['packages'], 'code');
      
               $packages = TweetcellKontor::whereIn('kupur', $codes)->get();
               foreach ($packages as $package) {
                // العثور على الباقة المناسبة من البيانات القادمة من الـ API
                $packageData = collect($data['packages'])->firstWhere('code', $package->kupur);
                 $section=TweetcellKontorSection::where('id',$package->section_id)->first();
               
                 if ($packageData) {
                    // تحديث السعر (basic_price)
                    $package->basic_price = $packageData['price'];
                  
                    $package->name = $packageData['name'];
                    $package->price = $packageData['price']+$packageData['price']* $section->increase_percentage/100;
                    $package->save(); // حفظ التحديث في قاعدة البيانات
                }
                 
              }
              // return response()->json(["packeges"=>$packages]);
           
           } 
       } 
    }
}*/
