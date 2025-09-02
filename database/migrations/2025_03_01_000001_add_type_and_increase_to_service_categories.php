<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('service_categories', 'type')) {
                $table->unsignedTinyInteger('type')->default(1)->after('description');
            }
            if (!Schema::hasColumn('service_categories', 'increase_percentage')) {
                $table->unsignedDecimal('increase_percentage', 5, 2)->default(0)->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            if (Schema::hasColumn('service_categories', 'increase_percentage')) {
                $table->dropColumn('increase_percentage');
            }
            if (Schema::hasColumn('service_categories', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
