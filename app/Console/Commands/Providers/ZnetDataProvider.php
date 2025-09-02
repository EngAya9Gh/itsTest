<?php
namespace App\Providers;

use Illuminate\Support\Facades\Http;
use App\Models\Tweetcell;
use App\Models\TweetcellSection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZnetDataProvider
{
    public function fetchData()
    {
        Log::info('بدء تحديث بيانات Znet...');
        $setting = \App\Models\Setting::first();
        if (!$setting || !$setting->znet_user_name || !$setting->znet_password) {
            Log::warning('Znet credentials are missing in settings table');
            return [];
        }

        $data = [
            'kod' => $setting->znet_user_name,
            'sifre' => $setting->znet_password,
        ];

        $response = Http::timeout(30)->get('https://bayi.tweetpin.net/servis/pin_listesi.php', $data);

        if ($response->failed()) {
            Log::error('فشل في جلب البيانات من Znet: HTTP '.$response->status());
            return [];
        }

        // Try JSON first
        $json = null;
        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            $json = null;
        }
        if (is_array($json) && isset($json['result']) && is_array($json['result'])) {
            Log::info('Znet returned JSON with '.count($json['result']).' items');
            return $json['result'];
        }

        // Fallback: log raw body snippet to inspect format
        $body = $response->body();
        Log::warning('Znet returned non-JSON response. Snippet: '.substr($body, 0, 400));

        // Heuristic parser for pipe-delimited lines starting with OK|...
        $parsed = $this->parsePipeResponse($body);
        if (!empty($parsed)) {
            Log::info('Parsed Znet pipe response items: '.count($parsed));
        }
        return $parsed;
    }

    private function parsePipeResponse(string $body): array
    {
        $lines = preg_split("/\r?\n/", trim($body));
        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') { continue; }
            if (Str::startsWith($line, 'OK')) {
                // remove leading OK and optional delimiter
                $line = ltrim(substr($line, 2), "| \t");
            }
            $parts = array_map('trim', explode('|', $line));
            if (count($parts) < 5) { continue; }

            // Guess mapping: try to detect numeric fields for kupur & fiyat
            $nums = [];
            foreach ($parts as $p) {
                if (preg_match('/^-?\d+(?:\.\d+)?$/', str_replace(',', '.', $p))) {
                    $nums[] = $p;
                }
            }
            $kupur = isset($nums[0]) ? (int)$nums[0] : null;
            $fiyat = isset($nums[1]) ? (float)str_replace(',', '.', $nums[1]) : null;

            $item = [
                'oyun_bilgi_id' => (int)($parts[count($parts)-1] ?? 0),
                'oyun_id'       => (int)($parts[0] ?? 0),
                'oyun_adi'      => $parts[1] ?? ($parts[0] ?? ''),
                'adi'           => $parts[2] ?? ($parts[1] ?? ''),
                'kupur'         => $kupur,
                'fiyat'         => $fiyat,
            ];
            // ensure minimum meaningful fields
            if ($item['oyun_id'] || $item['oyun_bilgi_id']) {
                $items[] = $item;
            }
        }
        return $items;
    }

    public function processGame($item)
    {
        $data = [
            'id' => $item['oyun_bilgi_id'],
            'section_id' => $item['oyun_id'],
            'section_name' => strip_tags((string)$item['oyun_adi']),
            'name' => strip_tags((string)$item['adi']),
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
        Log::info('Znet games fetched: '.count($games));

        foreach ($games as $game) {
            $this->processGame($game);
        }

        TweetcellSection::where('is-found', 0)->update(['status' => 0]);
        Tweetcell::where('is-found', 0)->update(['status' => 0]);

        Log::info("تم تحديث بيانات Znet بنجاح.");
    }
}
