<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Providers\ZnetDataProvider;

class UpdateZnetData extends Command
{
    protected $signature = 'znet:update';
    protected $description = 'تحديث بيانات Znet';

    public function handle()
    {
        $provider = new ZnetDataProvider();
        $provider->updateAll();
        $this->info('تم تحديث بيانات Znet بنجاح.');
    }
}
