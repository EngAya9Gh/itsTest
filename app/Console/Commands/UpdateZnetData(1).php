<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tweetcell;
use App\Models\Setting;
use App\Models\TweetcellSection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateZnetData extends Command
{  /*
api_no:znet=1,masar=2
*/
    protected $signature = 'znetttt:update';

    public function handle()
    {
        Log::info('Start updating data from Znet and Masar APIs...');

        // إعادة تعيين الحالة
        TweetcellSection::query()->update(['is-found' => 0]);
        Tweetcell::query()->update(['is-found' => 0]);

        $newSectionsCount = 0;
        $newTweetcellsCount = 0;

        // جلب بيانات من Znet
      $znetGames = $this->fetchZnetData();
        foreach ($znetGames as $game) {
            $normalized = $this->normalizeApiData($game, 'znet');
            [$sectionCreated, $cellCreated] = $this->processGame($normalized, 'znet');
            if ($sectionCreated) $newSectionsCount++;
            if ($cellCreated) $newTweetcellsCount++;
        }


        // جلب بيانات من Masar
        $masarGames = $this->fetchMasarData();
        foreach ($masarGames as $game) {
            $normalized = $this->normalizeApiData($game, 'masar');
            [$sectionCreated, $cellCreated] = $this->processGame($normalized, 'masar');
            if ($sectionCreated) $newSectionsCount++;
            if ($cellCreated) $newTweetcellsCount++;
        }

        // تحديث الحالة حسب is-found
        TweetcellSection::where('is-found', 1)->where('status', 0)->update(['status' => 1]);
        TweetcellSection::where('is-found', 0)->update(['status' => 0]);

        Tweetcell::where('is-found', 1)->where('status', 0)->update(['status' => 1]);
        Tweetcell::where('is-found', 0)->update(['status' => 0]);

        $this->info("تم التحديث بنجاح. عدد الأقسام الجديدة: $newSectionsCount. عدد الخدمات الجديدة: $newTweetcellsCount.");
    }

    private function fetchZnetData()
    {
        $setting = Setting::first();
        $data = [
            'kod' => $setting->znet_user_name,
            'sifre' => $setting->znet_password,
        ];

        $response = Http::get('https://bayi.tweetpin.net/servis/pin_listesi.php', $data);

        if ($response->failed()) {
            $this->error('فشل في جلب البيانات من Znet');
            return [];
        }

        return $response->json()['result'] ?? [];
    }

    private function fetchMasarData()
    {
        $response = Http::withHeaders([
            'api-token' => '540986d0b4307c6f4109885660b6867cd4d2b71f65de4fdd',
        ])->get('https://api.masar-card.com/client/api/products');

        if ($response->failed()) {
            $this->error('فشل في جلب البيانات من Masar');
            return [];
        }

        return $response->json();
    }
     public function extractAmountFromName($name, $categoryName)
        {
            // 1. إزالة اسم التصنيف من الاسم (بداية أو أي مكان آخر)
            $cleanName = str_ireplace($categoryName, '', $name); // تجاهل حالة الأحرف
        
            // 2. إزالة الرموز مثل $ أو (USA) إلخ
            $cleanName = preg_replace('/[^0-9.]/', ' ', $cleanName); // فقط أرقام ونقطة
        
            // 3. البحث عن أول رقم ظاهر
            preg_match('/\d+(\.\d+)?/', $cleanName, $matches);
            info(" amount: " . json_encode($cleanName));
            return isset($matches[0]) ? (float) $matches[0] : null;
        }

    private function normalizeApiData($item, $source)
    {
        if ($source === 'znet') {
            return [
                'id' => $item['oyun_bilgi_id'],
                'section_id' => $item['oyun_id'],
                'section_name' =>strip_tags( $item['oyun_adi']),
                'name' => $item['adi'],
                'amount' => $item['kupur'],
                'price' => $item['fiyat'],
                'base_price' => $item['fiyat'],
                'image' => null,
                'image_url' => null,
            ];
        }

        if ($source === 'masar') {
              $params = $item['params'];
    
    // طباعة محتوى params في التيرمينال
    echo "Params for item ID {$item['id']}:\n";
    print_r($params);
            return [
                'id' => $item['id'],
                'section_id' => $item['parent_id'],
                'section_name' =>strip_tags( $item['category_name']),
                'name' =>strip_tags( $item['name']),
                'amount' => $this->extractAmountFromName($item['name'], $item['category_name']),
                'price' => $item['price'],
                'base_price' => $item['base_price'],
                'image' => null,
                'image_url' => $item['category_img'] ?? null,
            ];
        }

        return null;
    }
   

    private function processGame($data, $source)
    {
        $section = TweetcellSection::updateOrCreate(
            ['id' => $data['section_id']],
            [  
                'name' => $data['section_name'],
                'is-found' => 1,
                'image' => $data['image'],
                'image_url' => $data['image_url'],
                'type' => 0,
                 'api_no' => $source === 'masar' ? 2 : 1,

            ]
        );

        $wasSectionNew = $section->wasRecentlyCreated;

        // تحديد شروط البحث بناءً على المصدر
        if ($source === 'masar') {
            $conditions = [
                'section_id' => $section->id,
                'name' => $data['name'],
            ];
        } else {
            $conditions = [
                'section_id' => $section->id,
                'amount' => $data['amount'],
                'player_no' => $data['id'],
            ];
        }

        $existing = Tweetcell::where($conditions)->first();

        $tweetcell = Tweetcell::updateOrCreate(
            $conditions,
            [
                'name' => $data['name'],
                'basic_price' => $data['base_price'],
                'price' => $this->calculatePrice($existing, ['fiyat' => $data['base_price']], $section),
                'is-found' => 1,
                'image' => $data['image'],
                'image_url' => $data['image_url'],
                'amount' => $source === 'masar' ? null : $data['amount'],
                'player_no' => $source === 'masar' ? null : $data['id'],
            ]
        );

        return [$wasSectionNew, $tweetcell->wasRecentlyCreated];
    }

    private function calculatePrice($tweetcell, $gameData, $section)
    {
        if ($tweetcell) {
            $price = $tweetcell->basic_price == floatval($gameData['fiyat'])
                ? $tweetcell->price
                : (floatval($gameData['fiyat']) + floatval($gameData['fiyat']) * ($section->increase_percentage ?? 0) / 100);
            return round($price, 4);
        }

        $price = floatval($gameData['fiyat']) + floatval($gameData['fiyat']) * ($section->increase_percentage ?? 0) / 100;
        return round($price, 4);
    }
}
