<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('PRAXITESTS_ADMIN_EMAIL', 'admin@praxitests.local');
        $password = env('PRAXITESTS_ADMIN_PASSWORD', 'changeme123');
        $name     = env('PRAXITESTS_ADMIN_NAME', 'Administrateur');

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        if ($this->command) {
            $this->command->info("Admin user ready: {$email}");
            if ($password === 'changeme123') {
                $this->command->warn('⚠ Change le mot de passe par défaut "changeme123" immédiatement.');
            }
        }
    }
}
