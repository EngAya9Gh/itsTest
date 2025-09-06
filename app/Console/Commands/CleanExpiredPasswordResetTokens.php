<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanExpiredPasswordResetTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired password reset tokens from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning expired password reset tokens...');

        // حذف الـ tokens المنتهية الصلاحية (أكثر من 60 دقيقة)
        $deletedCount = DB::table('password_reset_tokens')
            ->where('created_at', '<', Carbon::now()->subMinutes(60))
            ->delete();

        $this->info("Deleted {$deletedCount} expired password reset tokens.");

        return Command::SUCCESS;
    }
}
