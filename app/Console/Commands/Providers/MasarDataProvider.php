<?php
namespace App\Console\Commands\Providers;

use Illuminate\Support\Facades\Http;
use App\Models\Tweetcell;
use App\Models\TweetcellSection;
use Illuminate\Support\Facades\Log;

class MasarDataProvider
{
    private $token = 'b74d86453d879b6fb9582b12dba2a9eb2519949b4f0fed7c';

    private function fetchData()
    {
        Log::info('بدء تحديث بيانات Masar...');
        $listResponse = Http::withHeaders([
            'api-token' => $this->token,
        ])->get('https://api.masar-card.com/client/api/products');

        if ($listResponse->failed()) {
            Log::error('فشل في جلب القائمة الأساسية من Masar');
            return [];
        }

        // فلترة المنتجات المتاحة فقط
        return collect($listResponse->json())
            ->where('available', true)
            ->values()
            ->toArray();
    }

    private function fetchProductDetails($productId)
    {
        $url = "https://api.masar-card.com/client/api/products?products_id={$productId}";

        $response = Http::withHeaders([
            'api-token' => $this->token,
        ])->get($url);

        if ($response->failed()) {
            Log::warning("فشل في جلب تفاصيل المنتج: {$productId}");
            return null;
        }

        $data = $response->json();

        // أحيانًا الاستجابة تكون [ ] أو { } لازم نرجع أول عنصر لو مصفوفة
        return is_array($data) && isset($data[0]) ? $data[0] : $data;
    }

    public function updateAll()
    {
        TweetcellSection::query()->update(['is-found' => 0]);
        Tweetcell::query()->update(['is-found' => 0]);

        $products = $this->fetchData();

        $sectionsData = [];
        $tweetcellsData = [];

        foreach ($products as $product) {
            // جلب التفاصيل الخاصة بكل منتج
            $details = $this->fetchProductDetails($product['id']);
            if (!$details) {
                continue;
            }

            $qtyValues = $details['qty_values'] ?? null;
            $param     = $details['params'][0] ?? null; // الآن نأخذه من التفاصيل وليس من القائمة
            $amount    = 1;

            if (is_array($qtyValues)) {
                // لو قيم محددة ["110","150",...] ناخد أول قيمة
                $amount = isset($qtyValues[0]) ? (float)$qtyValues[0] : 1;
            } elseif (is_array($qtyValues) && isset($qtyValues['min'])) {
                // لو كان range {"min":"500","max":"500000"} ناخد min
                $amount = (float)($qtyValues['min'] ?? 1);
            }

            $sectionsData[] = [
                'id' => $product['parent_id'],
                'name' => strip_tags($product['category_name']),
                'is-found' => 1,
                'api_no' => 2,
                'status' => 1,
            ];

            $tweetcellsData[] = [
                'section_id'  => $product['parent_id'],
                'product_id'  => $product['id'],
                'name'        => strip_tags($product['name']),
                'basic_price' => $product['base_price'],
                'price'       => $product['price'],
                'is-found'    => 1,
                'amount'      => $amount,
                'player_no'   => $product['id'],
                'param'       => $param,  // من التفاصيل
                'status'      => 1,
                'qty_values'  => is_array($qtyValues) ? json_encode($qtyValues) : null, // تخزين كل التفاصيل
            ];
        }

        // upsert أقوى وأسرع
        TweetcellSection::upsert($sectionsData, ['id'], ['name', 'is-found', 'api_no', 'status']);
        Tweetcell::upsert($tweetcellsData, ['product_id'], [
            'section_id', 'name', 'basic_price', 'price', 'is-found', 'amount', 'param', 'status', 'qty_values'
        ]);

        // أي منتج لم يتحدث → يتوقف
        Tweetcell::where('is-found', 0)->update(['status' => 0]);
        TweetcellSection::where('is-found', 0)->update(['status' => 0]);

        Log::info("تم تحديث بيانات Masar بنجاح.");
    }
}
