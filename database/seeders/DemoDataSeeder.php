<?php

namespace Database\Seeders;

use App\Models\AppSettings;
use App\Models\Client;
use App\Models\ClientMeasurement;
use App\Models\MeasurementFormTemplate;
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
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $faker = FakerFactory::create('fr_FR');

        AppSettings::query()->updateOrCreate(
            ['id' => 1],
            [
                'business_name' => 'Maison Couleur — Dakar',
                'settings' => ['city' => 'Dakar', 'country' => 'SN', 'phone' => '+221 33 000 00 00'],
            ],
        );

        $this->clearDemoData();

        MeasurementFormTemplate::seedDefaultsIfEmpty();

        foreach ($this->demoAccounts() as $account) {
            User::query()->create([
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

        (new TenantBusinessDemoGenerator($faker))->generate($owner, $clients);
    }

    protected function clearDemoData(): void
    {
        DB::transaction(function (): void {
            DB::table('commerce_cart_items')->delete();
            DB::table('notification_logs')->delete();
            DB::table('reporting_snapshots')->delete();
            DB::table('product_images')->delete();
            DB::table('products')->delete();
            DB::table('payments')->delete();
            DB::table('invoice_items')->delete();
            DB::table('invoices')->delete();
            DB::table('quote_items')->delete();
            DB::table('quotes')->delete();
            DB::table('order_assignments')->delete();
            DB::table('order_status_histories')->delete();
            DB::table('order_images')->delete();
            DB::table('order_items')->delete();
            DB::table('orders')->delete();
            DB::table('stock_movements')->delete();
            DB::table('stock_alerts')->delete();
            DB::table('inventory_items')->delete();
            DB::table('cash_movements')->delete();
            DB::table('daily_cash_closures')->delete();
            DB::table('finance_categories')->delete();
            DB::table('fixed_assets')->delete();
            DB::table('staff_performances')->delete();
            DB::table('staff_tasks')->delete();
            DB::table('employees')->delete();
            DB::table('client_measurements')->delete();
            DB::table('measurement_form_templates')->delete();
            DB::table('inventory_form_templates')->delete();
            DB::table('clients')->delete();

            $emails = collect($this->demoAccounts())->pluck('email')->all();
            User::query()->whereIn('email', $emails)->delete();
        });
    }
}
