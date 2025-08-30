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
        Schema::create('tweetcells', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 8, 4);
            
            $table->decimal('basic_price', 8, 4);
            $table->integer('section_id');
            $table->foreign('section_id')->references('id')->on('app_sections')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->json('qty_values')->nullable()->after('amount');
            $table->string('note')->nullable();
            $table->string('image_url')->nullable();
            $table->string('player_no')->nullable();

            $table->tinyInteger('status')->default('1');
            $table->timestamps();
  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tweetcell');
    }
};
