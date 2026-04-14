<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientMeasurement;
use App\Models\MeasurementFormTemplate;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\Support\TenantBusinessDemoGenerator;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /** @return list<array{email: string, name: string, role: string}> */
    private function demoAccounts(): array
    {
        return [
            ['email' => 'proprietaire@demo.mansavibes.test', 'name' => 'Amadou Diallo', 'role' => 'owner'],
            ['email' => 'manager@demo.mansavibes.test', 'name' => 'Fatou Ndiaye', 'role' => 'manager'],
            ['email' => 'tailleur@demo.mansavibes.test', 'name' => 'Ibrahima Sow', 'role' => 'tailor'],
            ['email' => 'comptable@demo.mansavibes.test', 'name' => 'Aissatou Ba', 'role' => 'accountant'],
            ['email' => 'commercial@demo.mansavibes.test', 'name' => 'Moussa Kane', 'role' => 'sales'],
        ];
    }

    public function run(): void
    {
        $faker = FakerFactory::create('fr_FR');

        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => 'demo-atelier'],
            [
                'name' => 'Maison Couleur — Dakar',
                'settings' => ['city' => 'Dakar', 'country' => 'SN', 'phone' => '+221 33 000 00 00'],
            ],
        );

        $this->clearTenantDemoData($tenant->id);

        MeasurementFormTemplate::seedDefaultsForTenantId((int) $tenant->id);

        foreach ($this->demoAccounts() as $account) {
            User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => 'password',
                'role' => $account['role'],
            ]);
        }

        $owner = User::query()->where('email', 'proprietaire@demo.mansavibes.test')->firstOrFail();

        $clients = collect();
        foreach (range(1, 16) as $i) {
            $clients->push(Client::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $faker->name(),
                'phone' => '+221 '.random_int(70, 79).' '.$faker->numerify('### ## ##'),
                'email' => $faker->unique()->safeEmail(),
                'notes' => $faker->optional(0.4)->paragraph(),
                'balance_cents' => $faker->numberBetween(-35_000, 85_000),
            ]));
        }

        foreach ($clients as $client) {
            foreach (range(1, $faker->numberBetween(1, 3)) as $_round) {
                $poitrine = $faker->randomFloat(2, 82, 118);
                $taille = $faker->randomFloat(2, 62, 98);
                $hanche = $faker->randomFloat(2, 88, 124);
                $longueur = $faker->randomFloat(2, 95, 125);
                $epaule = $faker->randomFloat(2, 38, 52);

                ClientMeasurement::query()->create([
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'label' => $faker->randomElement(['Couture femme', 'Costume homme', 'Enfant', 'Boubou', 'Ensemble cocktail']),
                    'data' => [
                        'poitrine_cm' => $poitrine,
                        'taille_cm' => $taille,
                        'hanche_cm' => $hanche,
                        'longueur_cm' => $longueur,
                        'epaule_cm' => $epaule,
                    ],
                    'poitrine_cm' => $poitrine,
                    'taille_cm' => $taille,
                    'hanche_cm' => $hanche,
                    'longueur_cm' => $longueur,
                    'epaule_cm' => $epaule,
                    'custom_measures' => $faker->boolean(40) ? [
                        ['label' => 'Tour de bras', 'value' => (string) $faker->numberBetween(28, 36), 'unit' => 'cm'],
                        ['label' => 'Tour de cou', 'value' => (string) $faker->numberBetween(36, 42), 'unit' => 'cm'],
                    ] : [],
                    'measurement_notes' => $faker->optional(0.35)->sentence(),
                ]);
            }
        }

        (new TenantBusinessDemoGenerator($faker))->generate($tenant, $owner, $clients);
    }

    protected function clearTenantDemoData(int $tenantId): void
    {
        DB::transaction(function () use ($tenantId): void {
            DB::table('commerce_cart_items')->where('tenant_id', $tenantId)->delete();
            DB::table('notification_logs')->where('tenant_id', $tenantId)->delete();
            DB::table('reporting_snapshots')->where('tenant_id', $tenantId)->delete();
            DB::table('product_images')->where('tenant_id', $tenantId)->delete();
            DB::table('products')->where('tenant_id', $tenantId)->delete();
            DB::table('payments')->where('tenant_id', $tenantId)->delete();
            DB::table('invoice_items')->where('tenant_id', $tenantId)->delete();
            DB::table('invoices')->where('tenant_id', $tenantId)->delete();
            DB::table('quote_items')->where('tenant_id', $tenantId)->delete();
            DB::table('quotes')->where('tenant_id', $tenantId)->delete();
            DB::table('order_assignments')->where('tenant_id', $tenantId)->delete();
            DB::table('order_status_histories')->where('tenant_id', $tenantId)->delete();
            DB::table('order_images')->where('tenant_id', $tenantId)->delete();
            DB::table('order_items')->where('tenant_id', $tenantId)->delete();
            DB::table('orders')->where('tenant_id', $tenantId)->delete();
            DB::table('stock_movements')->where('tenant_id', $tenantId)->delete();
            DB::table('stock_alerts')->where('tenant_id', $tenantId)->delete();
            DB::table('inventory_items')->where('tenant_id', $tenantId)->delete();
            DB::table('cash_movements')->where('tenant_id', $tenantId)->delete();
            DB::table('daily_cash_closures')->where('tenant_id', $tenantId)->delete();
            DB::table('finance_categories')->where('tenant_id', $tenantId)->delete();
            DB::table('staff_performances')->where('tenant_id', $tenantId)->delete();
            DB::table('staff_tasks')->where('tenant_id', $tenantId)->delete();
            DB::table('employees')->where('tenant_id', $tenantId)->delete();
            DB::table('client_measurements')->where('tenant_id', $tenantId)->delete();
            DB::table('measurement_form_templates')->where('tenant_id', $tenantId)->delete();
            DB::table('clients')->where('tenant_id', $tenantId)->delete();

            User::query()->where('tenant_id', $tenantId)->delete();
        });
    }
}
