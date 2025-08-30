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
        Schema::create('card_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('card_id');
            $table->integer('user_id');
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('price', 8, 4);
            $table->integer('count');
            $table->string('note')->nullable();
            
            $table->string('status')->nullable()->default('قيد المراجعة');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_orders');
    }
};
