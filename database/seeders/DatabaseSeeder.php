<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@northumbria.ac.uk',
        ]);
        $admin->assignRole('Admin');

        // Create a student user for testing
        $student = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'student@northumbria.ac.uk',
        ]);
        $student->assignRole('Student');

        // Create a security staff user for testing
        $security = User::factory()->create([
            'name' => 'Security Staff',
            'email' => 'security@northumbria.ac.uk',
        ]);
        $security->assignRole('Security Staff');
    }
}
