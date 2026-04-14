<?php

namespace Database\Seeders;

use App\Models\AppSettings;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Comptes principaux : connexion par numéro de téléphone (pas d’e-mail).
 */
class PrimaryAccountsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            DB::table('sessions')->delete();
            DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->delete();

            Employee::query()->delete();
            User::query()->delete();
        });

        AppSettings::query()->updateOrCreate(
            ['id' => 1],
            ['business_name' => 'Vetement Palace']
        );

        $tailleur = User::query()->create([
            'name' => 'Alassane',
            'phone' => '8063629836',
            'password' => 'AlascoVP',
            'role' => 'tailleur',
        ]);

        User::query()->create([
            'name' => 'Administrateur',
            'phone' => '74335905',
            'password' => 'FakolyFaf',
            'role' => 'owner',
        ]);

        Employee::query()->create([
            'user_id' => $tailleur->id,
            'name' => 'Alassane',
            'phone' => null,
            'role_title' => 'Tailleur',
        ]);

        $this->command?->info('Comptes — tailleur : 8063629836 / AlascoVP · admin : 74335905 / FakolyFaf');
    }
}
