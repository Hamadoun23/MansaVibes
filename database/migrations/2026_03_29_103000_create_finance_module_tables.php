<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('expense');
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
        });

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finance_category_id')->nullable()->constrained('finance_categories')->nullOnDelete();
            $table->string('direction')->default('out');
            $table->bigInteger('amount_cents');
            $table->string('label');
            $table->date('movement_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'movement_date']);
        });

        Schema::create('daily_cash_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('closed_on');
            $table->bigInteger('opening_cents')->default(0);
            $table->bigInteger('closing_cents')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'closed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_cash_closures');
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('finance_categories');
    }
};
