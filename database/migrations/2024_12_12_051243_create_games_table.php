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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('image_url')->nullable();

            $table->integer('section_id');
          
            $table->integer('user_id_game')->nullable();
            $table->integer('amount');
            $table->foreign('section_id')->references('id')->on('game_sections')->onDelete('cascade');
            $table->decimal('price', 10, 4)->nullable();
            $table->decimal('basic_price', 10, 4)->nullable();
            $table->string('note')->nullable();
            $table->tinyInteger('status')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
