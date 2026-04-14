<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_categories', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('monthly_salary_cents')->default(0);
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('acquisition_date');
            $table->unsignedBigInteger('amount_cents');
            $table->unsignedSmallInteger('useful_life_months')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'acquisition_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('monthly_salary_cents');
        });

        Schema::table('finance_categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
