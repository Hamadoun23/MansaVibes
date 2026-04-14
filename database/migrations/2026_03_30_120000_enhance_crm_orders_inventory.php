<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_measurements', function (Blueprint $table) {
            $table->decimal('poitrine_cm', 6, 2)->nullable()->after('label');
            $table->decimal('taille_cm', 6, 2)->nullable();
            $table->decimal('hanche_cm', 6, 2)->nullable();
            $table->decimal('longueur_cm', 6, 2)->nullable();
            $table->decimal('epaule_cm', 6, 2)->nullable();
            $table->json('custom_measures')->nullable();
            $table->text('measurement_notes')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('model_name')->nullable()->after('reference');
            $table->unsignedBigInteger('advance_payment_cents')->default(0)->after('total_cents');
            $table->text('model_notes')->nullable()->after('advance_payment_cents');
        });

        Schema::create('order_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('stock_type')->default('fabric')->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('stock_type');
        });

        Schema::dropIfExists('order_images');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['model_name', 'advance_payment_cents', 'model_notes']);
        });

        Schema::table('client_measurements', function (Blueprint $table) {
            $table->dropColumn([
                'poitrine_cm', 'taille_cm', 'hanche_cm', 'longueur_cm', 'epaule_cm',
                'custom_measures', 'measurement_notes',
            ]);
        });
    }
};
