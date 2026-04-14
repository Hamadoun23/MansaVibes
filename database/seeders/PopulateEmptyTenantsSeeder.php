<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientMeasurement;
use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\MeasurementFormTemplate;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\Support\TenantBusinessDemoGenerator;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

/**
 * Complète les tenants « vides » : clients + mensurations si besoin,
 * puis tous les modules métier (commandes, stock, finance, e-commerce, etc.)
 * lorsqu'aucune donnée employés / commandes / inventaire n'existe encore.
 */
class PopulateEmptyTenantsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('fr_FR');

        $tenants = Tenant::query()->orderBy('id')->get();

        foreach ($tenants as $tenant) {
            MeasurementFormTemplate::seedDefaultsForTenantId((int) $tenant->id);

            $hasClients = Client::query()->withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->exists();

            if (! $hasClients) {
                foreach (range(1, 16) as $i) {
                    $client = Client::query()->withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'name' => $faker->name(),
                        'phone' => '+221 '.random_int(70, 79).' '.$faker->numerify('### ## ##'),
                        'email' => $faker->unique()->safeEmail(),
                        'notes' => $faker->optional(0.35)->paragraph(),
                        'balance_cents' => $faker->numberBetween(-25_000, 65_000),
                    ]);

                    foreach (range(1, $faker->numberBetween(1, 2)) as $_) {
                        $poitrine = $faker->randomFloat(2, 82, 118);
                        $taille = $faker->randomFloat(2, 62, 98);
                        $hanche = $faker->randomFloat(2, 88, 124);
                        $longueur = $faker->randomFloat(2, 95, 125);
                        $epaule = $faker->randomFloat(2, 38, 52);

                        ClientMeasurement::query()->withoutGlobalScopes()->create([
                            'tenant_id' => $tenant->id,
                            'client_id' => $client->id,
                            'label' => $faker->randomElement(['Couture femme', 'Costume homme', 'Enfant', 'Boubou', 'Tenue cérémonie']),
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
                            'custom_measures' => $faker->boolean(35) ? [
                                ['label' => 'Tour de bras', 'value' => (string) $faker->numberBetween(28, 36), 'unit' => 'cm'],
                            ] : [],
                            'measurement_notes' => $faker->optional(0.25)->sentence(),
                        ]);
                    }
                }

                $this->command?->info('Clients fictifs ajoutés pour le tenant « '.$tenant->name.' » (slug: '.$tenant->slug.').');
            }

            $clients = Client::query()->withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->get();

            if ($clients->isEmpty()) {
                continue;
            }

            $needsBusinessSamples = ! Employee::query()->withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->exists()
                && ! Order::query()->withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->exists()
                && ! InventoryItem::query()->withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->exists();

            if (! $needsBusinessSamples) {
                continue;
            }

            $actor = User::query()->withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->first();

            if ($actor === null) {
                $this->command?->warn('Tenant « '.$tenant->name.' » : aucun utilisateur, modules métier non remplis.');

                continue;
            }

            (new TenantBusinessDemoGenerator($faker))->generate($tenant, $actor, $clients);

            $this->command?->info('Modules métier remplis pour « '.$tenant->name.' » (commandes, stock, finance, e-commerce, reporting, communications).');
        }
    }
}
