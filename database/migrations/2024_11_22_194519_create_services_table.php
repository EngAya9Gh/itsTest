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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('section_id')->constrained('service_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('image_url')->nullable();
            $table->text('note')->nullable();
            $table->string('type')->default('1');
            /* 1: imei
  2:  dfth new   username ,password,gmail, note
    3:  dfth old   username,note
   5: chemra username ,password,note
  4:  aftar count ,email note


    */
            $table->tinyInteger('status')->default('1');
            $table->decimal('price', 10, 2);
            $table->decimal('basic_price', 10, 2);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
