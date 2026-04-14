<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('inventory_characteristic_key', 64)->nullable()->after('inventory_item_id');
            $table->decimal('inventory_consumed_meters', 12, 3)->nullable()->after('inventory_characteristic_key');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['inventory_characteristic_key', 'inventory_consumed_meters']);
        });
    }
};
