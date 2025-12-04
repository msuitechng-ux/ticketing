<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Ticket management
            'view tickets',
            'create tickets',
            'edit tickets',
            'delete tickets',
            'download qr codes',

            // Ticket requests
            'request extra tickets',
            'approve ticket requests',
            'deny ticket requests',
            'view all ticket requests',

            // Graduate management
            'view graduates',
            'create graduates',
            'edit graduates',
            'delete graduates',
            'import graduates',

            // Ceremony management
            'view ceremonies',
            'create ceremonies',
            'edit ceremonies',
            'delete ceremonies',

            // Entry verification
            'scan qr codes',
            'verify entries',
            'view entry logs',

            // Analytics and reporting
            'view analytics',
            'view reports',
            'export reports',

            // System administration
            'manage users',
            'manage roles',
            'manage permissions',
            'view system logs',

            // Redistribution
            'manage redistribution',
            'view unused tickets',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        $studentRole = Role::create(['name' => 'Student']);
        $studentRole->givePermissionTo([
            'view tickets',
            'request extra tickets',
            'download qr codes',
        ]);

        $securityStaffRole = Role::create(['name' => 'Security Staff']);
        $securityStaffRole->givePermissionTo([
            'scan qr codes',
            'verify entries',
            'view entry logs',
        ]);

        $eventStaffRole = Role::create(['name' => 'Event Staff']);
        $eventStaffRole->givePermissionTo([
            'view tickets',
            'view graduates',
            'view ceremonies',
            'view analytics',
            'view reports',
            'scan qr codes',
            'verify entries',
            'view entry logs',
        ]);

        $registryRole = Role::create(['name' => 'Registry']);
        $registryRole->givePermissionTo([
            'view graduates',
            'create graduates',
            'edit graduates',
            'import graduates',
            'view tickets',
            'view reports',
        ]);

        $itSupportRole = Role::create(['name' => 'IT Support']);
        $itSupportRole->givePermissionTo([
            'view system logs',
            'view analytics',
            'view reports',
        ]);
    }
}
