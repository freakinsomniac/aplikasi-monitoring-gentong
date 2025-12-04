<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if not exists
        if (!User::where('email', 'admin@uptimemonitor.local')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@uptimemonitor.local',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin user created: admin@uptimemonitor.local (password: password)');
        }

        // Create regular user for testing
        if (!User::where('email', 'user@uptimemonitor.local')->exists()) {
            User::create([
                'name' => 'Regular User',
                'email' => 'user@uptimemonitor.local',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);

            $this->command->info('Regular user created: user@uptimemonitor.local (password: password)');
        }
    }
}
