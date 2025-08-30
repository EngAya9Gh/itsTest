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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('item_id');
            $table->enum('item_type', ['App', 'Ecard', 'Card', 'Ebank', 'DataCommunication', 'Game', 'Program','Service']);
            $table->timestamps();

            // إضافة فهرس مركب لتحسين أداء الاستعلامات التي تبحث باستخدام item_id و item_type
            $table->index(['item_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
