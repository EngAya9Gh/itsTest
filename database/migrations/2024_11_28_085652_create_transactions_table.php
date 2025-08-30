<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_user_id')->nullable(); // المستخدم المرسل
            $table->unsignedBigInteger('to_user_id')->nullable(); // المستخدم المستلم
            $table->decimal('amount', 15, 2); // المبلغ
            $table->decimal('remain_amount', 15, 2); // المبلغ
          $table->string('note')->nullable(); 
            $table->tinyInteger('payment_done')->default('1');
            $table->decimal('base_amount', 20, 2)->after('amount')->nullable();
            $table->unsignedBigInteger('base_currency_id')->after('base_amount')->nullable();
            $table->unsignedBigInteger('target_currency_id')->after('base_currency_id')->nullable();
            $table->decimal('rate_at_time', 12, 6)->after('target_currency_id')->nullable();

            $table->foreign('base_currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->foreign('target_currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->timestamps();
        
            // العلاقات
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
