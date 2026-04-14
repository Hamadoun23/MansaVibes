<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('quality_label', 191)->nullable()->after('description');
            $table->json('colors')->nullable()->after('quality_label');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'quality_label', 'colors']);
        });
    }
};
