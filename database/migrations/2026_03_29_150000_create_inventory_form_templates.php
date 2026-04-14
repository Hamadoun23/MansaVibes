<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_form_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('applies_to_stock_type')->nullable();
            $table->json('fields');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active', 'sort_order']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('inventory_form_template_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('inventory_form_templates')
                ->nullOnDelete();
            $table->json('characteristic_values')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inventory_form_template_id');
            $table->dropColumn('characteristic_values');
        });

        Schema::dropIfExists('inventory_form_templates');
    }
};
