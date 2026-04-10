<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrateur Principal
        User::create([
            'name' => 'Administrateur YA',
            'email' => 'admin@yaconsulting.ci',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'theme' => 'light',
            'email_verified_at' => now(),
        ]);

        // 2. Chef de Projet
        User::create([
            'name' => 'Kouadio Jean',
            'email' => 'manager@yaconsulting.ci',
            'password' => Hash::make('password'),
            'role' => 'project_manager',
            'theme' => 'light',
            'email_verified_at' => now(),
        ]);

        // 3. Collaborateur (Staff)
        User::create([
            'name' => 'Amenan Sophie',
            'email' => 'staff@yaconsulting.ci',
            'password' => Hash::make('password'),
            'role' => 'staff_member',
            'theme' => 'dark',
            'email_verified_at' => now(),
        ]);
    }
}
