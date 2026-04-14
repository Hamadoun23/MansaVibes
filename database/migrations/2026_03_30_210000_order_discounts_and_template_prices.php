<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurement_form_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_price_cents')->default(0)->after('is_active');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('discount_scope')->default('none')->after('advance_payment_cents');
            $table->unsignedBigInteger('order_discount_cents')->default(0)->after('discount_scope');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('measurement_form_template_id')
                ->nullable()
                ->after('order_id')
                ->constrained('measurement_form_templates')
                ->nullOnDelete();
            $table->unsignedBigInteger('discount_cents')->default(0)->after('unit_price_cents');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['measurement_form_template_id']);
            $table->dropColumn(['measurement_form_template_id', 'discount_cents']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_scope', 'order_discount_cents']);
        });

        Schema::table('measurement_form_templates', function (Blueprint $table) {
            $table->dropColumn('reference_price_cents');
        });
    }
};
