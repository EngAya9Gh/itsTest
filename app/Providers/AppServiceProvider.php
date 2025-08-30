<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use App\Models\App;
use App\Models\DataCommunicationOrder;
use App\Models\GameOrder;
use App\Models\EcardOrder;
use App\Models\CardOrder;
use App\Models\ProgramOrder;
use App\Models\ServiceOrder;
use App\Models\TweetcellOrder;
use App\Jobs\UpdateOrderStatus;
use App\Observers\GeneralObserver;
use Illuminate\Support\Facades\Queue;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // يمكن إضافة تسجيل الخدمات هنا إذا لزم الأمر.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));


        TweetcellOrder::observe(GeneralObserver::class);


    }
}
