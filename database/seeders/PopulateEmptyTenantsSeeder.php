<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientMeasurement;
use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\MeasurementFormTemplate;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\Support\TenantBusinessDemoGenerator;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

/**
 * Complète l’instance si elle est encore vide : clients, mensurations, modules métier.
 */
class PopulateEmptyTenantsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('fr_FR');

        MeasurementFormTemplate::seedDefaultsIfEmpty();

        $hasClients = Client::query()->exists();

        if (! $hasClients) {
            foreach (range(1, 16) as $i) {
                $client = Client::query()->create([
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

                    ClientMeasurement::query()->create([
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

            $this->command?->info('Clients fictifs ajoutés.');
        }

        $clients = Client::query()->get();

        if ($clients->isEmpty()) {
            return;
        }

        $needsBusinessSamples = ! Employee::query()->exists()
            && ! Order::query()->exists()
            && ! InventoryItem::query()->exists();

        if (! $needsBusinessSamples) {
            return;
        }

        $actor = User::query()->orderBy('id')->first();

        if ($actor === null) {
            $this->command?->warn('Aucun utilisateur : modules métier non remplis.');

            return;
        }

        (new TenantBusinessDemoGenerator($faker))->generate($actor, $clients);

        $this->command?->info('Modules métier remplis (commandes, stock, finance, e-commerce, reporting, communications).');
    }
}
