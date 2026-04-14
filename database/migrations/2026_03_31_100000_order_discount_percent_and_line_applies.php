<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('discount_percent')->default(0)->after('order_discount_cents');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('discount_applies')->default(false)->after('discount_cents');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('discount_applies');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
