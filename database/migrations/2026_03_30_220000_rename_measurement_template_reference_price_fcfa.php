<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('measurement_form_templates', 'reference_price_cents')) {
            return;
        }

        Schema::table('measurement_form_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_price_fcfa')->default(0)->after('is_active');
        });

        DB::table('measurement_form_templates')->select('id', 'reference_price_cents')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('measurement_form_templates')->where('id', $row->id)->update([
                    'reference_price_fcfa' => (int) $row->reference_price_cents,
                ]);
            }
        });

        Schema::table('measurement_form_templates', function (Blueprint $table) {
            $table->dropColumn('reference_price_cents');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('measurement_form_templates', 'reference_price_fcfa')) {
            return;
        }

        Schema::table('measurement_form_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_price_cents')->default(0)->after('is_active');
        });

        DB::table('measurement_form_templates')->select('id', 'reference_price_fcfa')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('measurement_form_templates')->where('id', $row->id)->update([
                    'reference_price_cents' => (int) $row->reference_price_fcfa,
                ]);
            }
        });

        Schema::table('measurement_form_templates', function (Blueprint $table) {
            $table->dropColumn('reference_price_fcfa');
        });
    }
};
