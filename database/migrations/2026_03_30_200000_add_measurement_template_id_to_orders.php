<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('measurement_form_template_id')
                ->nullable()
                ->after('model_name')
                ->constrained('measurement_form_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['measurement_form_template_id']);
            $table->dropColumn('measurement_form_template_id');
        });
    }
};
