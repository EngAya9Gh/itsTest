<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // External provider service identifier (integer)
            $table->integer('external_id')->nullable()->after('name');
            $table->index('external_id');
            $table->unique(['section_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['section_id', 'external_id']);
            $table->dropIndex(['external_id']);
            $table->dropColumn('external_id');
        });
    }
};
