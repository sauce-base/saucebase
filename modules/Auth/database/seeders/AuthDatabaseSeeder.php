<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;

class AuthDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'chef@saucebase.dev'],
            [
                'name' => 'Admin Chef',
                'password' => bcrypt('secretsauce'),
            ]
        );

        // Assign the admin role to the admin user
        $adminUser->assignRole('admin');

        // Create test users for E2E tests
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('secretsauce'),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('user');
    }
}
