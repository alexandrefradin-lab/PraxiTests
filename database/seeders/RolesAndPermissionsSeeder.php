<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions clés (ne couvre pas tout, étendues par les plugins)
        $permissions = [
            // Plateforme
            'manage:platform',
            'manage:plugins',
            'manage:billing',

            // Tests
            'view:tests',
            'create:tests',
            'edit:tests',
            'delete:tests',

            // Campagnes
            'view:campaigns',
            'send:campaigns',

            // Leads
            'view:leads',
            'edit:leads',

            // Candidats
            'take:tests',
            'view:own_results',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Rôles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $pro = Role::firstOrCreate(['name' => 'professional', 'guard_name' => 'web']);
        $pro->syncPermissions([
            'view:tests', 'create:tests', 'edit:tests',
            'view:campaigns', 'send:campaigns',
            'view:leads', 'edit:leads',
        ]);

        $candidate = Role::firstOrCreate(['name' => 'candidate', 'guard_name' => 'web']);
        $candidate->syncPermissions([
            'take:tests', 'view:own_results',
        ]);
    }
}
