<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KEEP_TENANT_ID = 3;

    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        $tenant = DB::table('tenants')->where('id', self::KEEP_TENANT_ID)->first();
        $settings = [];
        if ($tenant !== null) {
            $raw = $tenant->settings ?? null;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $settings = is_array($decoded) ? $decoded : [];
            } elseif (is_array($raw)) {
                $settings = $raw;
            }
        }

        DB::table('app_settings')->insert([
            'business_name' => $tenant->name ?? config('app.name'),
            'settings' => json_encode($settings),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tenants')->where('id', '!=', self::KEEP_TENANT_ID)->delete();

        $this->stripTenantColumn('cash_movements', [
            'index' => [['tenant_id', 'movement_date']],
        ]);

        $this->stripTenantColumn('clients', [
            'index' => [['tenant_id', 'name']],
        ]);

        $this->stripTenantColumn('client_measurements');

        $this->stripTenantColumn('commerce_cart_items', [
            'index' => [['tenant_id', 'session_id']],
        ]);

        $this->stripTenantColumn('daily_cash_closures', [
            'unique' => [['tenant_id', 'closed_on']],
        ], [
            'unique' => ['closed_on'],
        ]);

        $this->stripTenantColumn('employees', [
            'index' => [['tenant_id', 'name']],
        ]);

        $this->stripTenantColumn('finance_categories', [
            'index' => [['tenant_id', 'type']],
        ]);

        $this->stripTenantColumn('fixed_assets', [
            'index' => [['tenant_id', 'acquisition_date']],
        ]);

        $this->stripTenantColumn('inventory_form_templates', [
            'index' => [['tenant_id', 'is_active', 'sort_order']],
        ]);

        $this->stripTenantColumn('inventory_items', [
            'index' => [['tenant_id', 'name']],
        ]);

        $this->stripTenantColumn('invoice_items');

        $this->stripTenantColumn('invoices', [
            'unique' => [['tenant_id', 'number']],
        ], [
            'unique' => ['number'],
        ]);

        $this->stripTenantColumn('measurement_form_templates', [
            'index' => [['tenant_id', 'is_active']],
        ]);

        $this->stripTenantColumn('notification_logs', [
            'index' => [['tenant_id', 'channel', 'status']],
        ]);

        $this->stripTenantColumn('order_assignments');

        $this->stripTenantColumn('order_images');

        $this->stripTenantColumn('order_items');

        $this->stripTenantColumn('orders', [
            'unique' => [['tenant_id', 'reference']],
            'index' => [['tenant_id', 'status']],
        ], [
            'unique' => ['reference'],
        ]);

        $this->stripTenantColumn('order_status_histories');

        $this->stripTenantColumn('payments');

        $this->stripTenantColumn('product_images');

        $this->stripTenantColumn('products', [
            'unique' => [['tenant_id', 'slug']],
        ], [
            'unique' => ['slug'],
        ]);

        $this->stripTenantColumn('quote_items');

        $this->stripTenantColumn('quotes');

        $this->stripTenantColumn('reporting_snapshots', [
            'index' => [['tenant_id', 'period_start', 'period_end']],
        ]);

        $this->stripTenantColumn('staff_performances', [
            'uniqueName' => 'staff_perf_period_unique',
        ], [
            'unique' => ['employee_id', 'period_year', 'period_month'],
            'uniqueName' => 'staff_perf_period_unique',
        ]);

        $this->stripTenantColumn('staff_tasks');

        $this->stripTenantColumn('stock_alerts');

        $this->stripTenantColumn('stock_movements');

        $this->stripTenantColumn('suppliers', [
            'index' => [['tenant_id', 'name']],
        ]);

        $this->stripTenantColumn('users');

        Schema::dropIfExists('tenants');
    }

    /**
     * @param  array{unique?: list<list<string>>, index?: list<list<string>>, uniqueName?: string}  $drops
     * @param  array{unique?: list<string>, uniqueName?: string}  $adds
     */
    private function stripTenantColumn(string $tableName, array $drops = [], array $adds = []): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'tenant_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $blueprint) use ($drops): void {
            $blueprint->dropForeign(['tenant_id']);

            if (isset($drops['unique'])) {
                foreach ($drops['unique'] as $cols) {
                    $blueprint->dropUnique($cols);
                }
            }

            if (isset($drops['uniqueName'])) {
                $blueprint->dropUnique($drops['uniqueName']);
            }

            if (isset($drops['index'])) {
                foreach ($drops['index'] as $cols) {
                    $blueprint->dropIndex($cols);
                }
            }

            $blueprint->dropColumn('tenant_id');
        });

        if ($adds !== []) {
            Schema::table($tableName, function (Blueprint $blueprint) use ($adds): void {
                if (isset($adds['unique'])) {
                    if (isset($adds['uniqueName'])) {
                        $blueprint->unique($adds['unique'], $adds['uniqueName']);
                    } else {
                        $blueprint->unique($adds['unique']);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
