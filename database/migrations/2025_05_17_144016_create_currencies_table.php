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
        Schema::create('currencies', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // USD, EUR, TRY, SYP...
        $table->string('name'); // US Dollar, Euro...
        $table->float('rate', 16, 6); // 1 TRY = ? currency
        $table->boolean('is_base')->default(false); // Only one should be true
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
