<?php namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\TweetcellKontorSection;
use App\Models\TweetcellKontor;
use Illuminate\Support\Facades\Http;

class UpdateKontorPackeges implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Start processing UpdateKontorPackeges job'); // بداية التنفيذ

        // الحصول على جميع الأقسام
        $sections = TweetcellKontorSection::all();

        foreach ($sections as $section) {
            \Log::info('Processing section: '.$section->name); // إضافة رسالة للقسم الجاري معالجته

            $username = "05534060015";
            $password = "0015aa";
            
            // إعداد البيانات للطلب
             $data = [
            'phone' => trim($username),
            'password' => trim($password),
            'operator' => trim($section->name),
     		   ];
            // رابط الـ API
            $url ='https://1kanal.pro/b2c-api/market/getPackages';//?phone=&password=&operator=SelamTelekom

            // إرسال الطلب
            $response = Http::get($url, $data);

            // التحقق من نجاح الاستجابة
            if ($response->successful()) {
                \Log::info('Successfully fetched packages for section: ' . $section->id); // نجاح استجابة الـ API

                // استخراج الحزم من الاستجابة
                $packages = $response->json()['packages'];

                if (!empty($packages)) {
                    \Log::info('Found ' . count($packages) . ' packages for section: ' . $section->id);

                    if ($section->id != 1) {
                        foreach ($packages as $package) {
                            \Log::info('Processing package code: ' . $package['code']); // طباعة كود الحزمة

                            // البحث عن TweetcellKontor بناءً على القسم والكود
                            $tweetcell = TweetcellKontor::where('section_id', $section->id)
                                ->where('code', $package['code'])
                                ->first();

                            // تحديث أو إنشاء TweetcellKontor
                            $createdTweetcell = TweetcellKontor::updateOrCreate(
                                [
                                    'section_id' => $section->id,
                                    'code' => $package['code'],
                                ],
                                [
                                    'name' => $package['name'],
                                    'basic_price' => $package['price'],
                                    'price' => $package['price'] + ($package['price'] * $section->increase_percentage / 100),
                                    'code' => $package['code'],
                                ]
                            );

                            // إذا تم إنشاء Tweetcell جديدًا
                            if ($createdTweetcell->wasRecentlyCreated) {
                                \Log::info('Created new TweetcellKontor with code: ' . $package['code']);
                            } else {
                                \Log::info('Updated existing TweetcellKontor with code: ' . $package['code']);
                            }
                        }
                    } else {
                        $codes = array_column($packages, 'code');
                        $existingPackages = TweetcellKontor::whereIn('kupur', $codes)->get();
                        foreach ($existingPackages as $package) {
                            $packageData = collect($packages)->firstWhere('code', $package->kupur);
                            if ($packageData) {
                                $section = TweetcellKontorSection::where('id', $package->section_id)->first();

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
