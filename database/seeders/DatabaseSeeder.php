<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the main user
        User::factory()->create([
            'name' => 'Charles Ikeseh',
            'email' => 'charlesikeseh@gmail.com',
            'password' => Hash::make('charymeld'), // <-- Set your password here
        ]);

        // Call other seeders
        $this->call([
            RolesAndPermissionsSeeder::class,
            MenuItemSeeder::class,
            RoleSeeder::class,
            FixUserRolesSeeder::class,
            //ServiceSeeder::class,
            ClientSeeder::class,
            TestimonialSeeder::class,
            AnalyticsDataSeeder::class,
            AttendanceSeeder::class,
            BlogSeeder::class,
        ]);
    }
}

