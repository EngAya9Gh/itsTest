<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreferredCurrencyIdToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // إضافة العمود كـ unsignedBigInteger (nullable يعني اختياري)
            $table->unsignedBigInteger('preferred_currency_id')->nullable()->after('password');

            // تعيين المفتاح الأجنبي
            $table->foreign('preferred_currency_id')->references('id')->on('currencies')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // حذف المفتاح الأجنبي ثم العمود عند التراجع
            $table->dropForeign(['preferred_currency_id']);
            $table->dropColumn('preferred_currency_id');
        });
    }
}
