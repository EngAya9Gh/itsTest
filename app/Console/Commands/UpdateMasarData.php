<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Providers\MasarDataProvider; // ✅ المسار الصحيح

class UpdateMasarData extends Command
{
    protected $signature = 'masar:update';
    protected $description = 'تحديث بيانات Masar';

    public function handle()
    {
        $provider = new MasarDataProvider();
        $provider->updateAll();
        $this->info('تم تحديث بيانات Masar بنجاح.');
    }
}
