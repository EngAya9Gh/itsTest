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
        Schema::create('tweetcell_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('tweetcell_id');
            $table->integer('user_id');
            $table->foreign('tweetcell_id')->references('id')->on('tweetcell')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable();
             $table->integer('player_no')->nullable();       
            $table->string('status')->nullable()->default('قيد المراجعة');
            $table->decimal('price', 8, 4);
            $table->integer('count')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tweetcell_order');
    }
};
