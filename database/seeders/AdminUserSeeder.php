<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('PRAXIQUEST_ADMIN_EMAIL', 'admin@praxiquest.local');
        $name  = env('PRAXIQUEST_ADMIN_NAME', 'Administrateur');

        $password = env('PRAXIQUEST_ADMIN_PASSWORD');
        if (empty($password) || in_array($password, ['changeme-immediately', 'changeme123'])) {
            $password = \Illuminate\Support\Str::random(32);
            if (isset($this->command)) {
                $this->command->info("Admin password generated: {$password}");
            }
        }

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
        }
    }
}
