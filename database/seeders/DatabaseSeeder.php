<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@booking.test',
            'role' => UserRole::Admin,
        ]);

        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@booking.test',
            'role' => UserRole::Customer,
        ]);

        $this->call(ServiceSeeder::class);
        $this->call(StaffSeeder::class);
    }
}
