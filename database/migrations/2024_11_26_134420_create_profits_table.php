<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->morphs('profitable'); // يضيف عمودين: profitable_id و profitable_type
            $table->decimal('profit_amount', 10, 2);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('user_currency_id')->nullable();
            $table->decimal('user_rate_at_time', 12, 6)->nullable();
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profits');
    }

};
