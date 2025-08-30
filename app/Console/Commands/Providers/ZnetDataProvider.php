<?php
namespace App\Providers;

use Illuminate\Support\Facades\Http;
use App\Models\Tweetcell;
use App\Models\TweetcellSection;
use Illuminate\Support\Facades\Log;

class ZnetDataProvider
{
    public function fetchData()
    {
        Log::info('بدء تحديث بيانات Znet...');
        $setting = \App\Models\Setting::first();
        $data = [
            'kod' => $setting->znet_user_name,
            'sifre' => $setting->znet_password,
        ];

        $response = Http::get('https://bayi.tweetpin.net/servis/pin_listesi.php', $data);

        if ($response->failed()) {
            Log::error('فشل في جلب البيانات من Znet');
            return [];
        }

        return $response->json()['result'] ?? [];
    }

    public function processGame($item)
    {
        $data = [
            'id' => $item['oyun_bilgi_id'],
            'section_id' => $item['oyun_id'],
            'section_name' => strip_tags($item['oyun_adi']),
            'name' => strip_tags($item['adi']),
            'amount' => $item['kupur'],
            'price' => $item['fiyat'],
            'base_price' => $item['fiyat'],
            'player_no' => $item['oyun_bilgi_id'],
            'param'=>'ايدي اللاعب',
            'is-found' => 1,
        ];

        $section = TweetcellSection::updateOrCreate(
            ['id' => $data['section_id']],
            [
                'name' => $data['section_name'],
                'is-found' => 1,
                'api_no' => 1,
            ]
        );

        $tweetcell = Tweetcell::updateOrCreate(
            [
                'section_id' => $section->id,
                'amount' => $data['amount'],
                'player_no' => $data['id'],
            ],
            [
                'name' => $data['name'],
                'basic_price' => $data['base_price'],
                'price' => $data['price'],
                'is-found' => 1,
                'amount' => $data['amount'],
                'player_no' => $data['player_no'],
                'param' => $data['param'],
            ]
        );

        return $tweetcell;
    }

    public function updateAll()
    {
        TweetcellSection::query()->update(['is-found' => 0]);
        Tweetcell::query()->update(['is-found' => 0]);

        $games = $this->fetchData();

        foreach ($games as $game) {
            $this->processGame($game);
        }

        TweetcellSection::where('is-found', 0)->update(['status' => 0]);
        Tweetcell::where('is-found', 0)->update(['status' => 0]);

        Log::info("تم تحديث بيانات Znet بنجاح.");
    }
}
