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
        Schema::create('ecard_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('ecard_id');
            $table->integer('user_id');
            $table->foreign('ecard_id')->references('id')->on('ecards')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('count')->nullable();
            $table->decimal('price', 8, 4);
            $table->string('note')->nullable();
            $table->string('mobile')->nullable();
            
            $table->string('status')->nullable()->default('قيد المراجعة');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */ 
    public function down(): void
    {
        Schema::dropIfExists('ecard_orders');
    }
};
