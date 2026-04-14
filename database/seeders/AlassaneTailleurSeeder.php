<?php

namespace Database\Seeders;

use App\Models\AppSettings;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlassaneTailleurSeeder extends Seeder
{
    public function run(): void
    {
        AppSettings::query()->updateOrCreate(
            ['id' => 1],
            ['business_name' => 'Vetement Palace']
        );

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
                'name' => $newEmail,
                'password' => Hash::make('AlascoVP'),
                'role' => 'tailleur',
            ]
        );

        Employee::query()->updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'name' => 'Alassane',
                'phone' => null,
                'role_title' => 'Tailleur',
            ]
        );

        $this->command?->info('Tailleur prêt (Vetement Palace). Connexion : '.$newEmail.' / AlascoVP');
    }
}
