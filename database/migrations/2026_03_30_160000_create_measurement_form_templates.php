<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_form_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('notes')->nullable();
            $table->json('fields');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::table('client_measurements', function (Blueprint $table) {
            $table->foreignId('measurement_form_template_id')
                ->nullable()
                ->after('client_id')
                ->constrained('measurement_form_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('client_measurements', function (Blueprint $table) {
            $table->dropForeign(['measurement_form_template_id']);
            $table->dropColumn('measurement_form_template_id');
        });

        Schema::dropIfExists('measurement_form_templates');
    }
};
