<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create an admin user
        User::create([
            'firstName' => 'Admin',
            'lastName' => 'User',
            'birthDate' => '1990-01-01',
            'city' => 'Paris',
            'country' => 'FR',
            'avatar' => 'https://via.placeholder.com/150',
            'company' => 'Users Generator Inc',
            'jobPosition' => 'Administrator',
            'mobile' => '+33612345678',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        
        // Create a regular user
        User::create([
            'firstName' => 'Test',
            'lastName' => 'User',
            'birthDate' => '1995-05-05',
            'city' => 'Lyon',
            'country' => 'FR',
            'avatar' => 'https://via.placeholder.com/150',
            'company' => 'Users Generator Inc',
            'jobPosition' => 'Tester',
            'mobile' => '+33687654321',
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        
        // Create 10 random users
        User::factory(10)->create();
    }
}
