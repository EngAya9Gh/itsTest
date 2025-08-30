<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
         /* 1: imei
  2:  dfth new   username ,password,email, note
    3:  dfth old   username,note
   5: chemra username ,password,note
  4:  aftar count ,email note


    */
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();

        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('ime')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('count')->nullable();
            $table->string('password')->nullable();
            
            $table->string('status')->nullable()->default('قيد المراجعة');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
