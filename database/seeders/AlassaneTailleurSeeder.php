<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlassaneTailleurSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->find(3);
        if ($tenant === null) {
            $this->command?->warn('Aucun tenant id=3 : créez d’abord la boutique ou ajustez l’id dans AlassaneTailleurSeeder.');

            return;
        }

        $tenant->name = 'Vetement Palace';
        if (! Tenant::query()->where('slug', 'vetement-palace')->where('id', '!=', $tenant->id)->exists()) {
            $tenant->slug = 'vetement-palace';
        }
        $tenant->save();

        $newEmail = 'alassane@vp.com';
        $legacyEmail = 'alassane@vetement-palace.local';

        $user = User::query()->where('email', $newEmail)->first();
        if ($user === null) {
            $user = User::query()->where('email', $legacyEmail)->first();
            if ($user !== null) {
                $user->email = $newEmail;
                $user->save();
            }
        }

        $user = User::query()->updateOrCreate(
            ['email' => $newEmail],
            [
                'tenant_id' => $tenant->id,
                'name' => $newEmail,
                'password' => Hash::make('AlascoVP'),
                'role' => 'tailleur',
            ]
        );

        Employee::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ],
            [
                'name' => 'Alassane',
                'phone' => null,
                'role_title' => 'Tailleur',
            ]
        );

        $this->command?->info('Tailleur prêt (boutique id3 — Vetement Palace). Connexion : '.$newEmail.' / AlascoVP');
    }
}
