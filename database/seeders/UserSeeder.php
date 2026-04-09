<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Administrateur
        User::create([
            'name'     => 'Administrateur YA',
            'email'    => 'adminyaconsulting.ci',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'theme'    => 'light',
            'email_verified_at' => now(),
        ]);

        // Chef de projet
        User::create([
            'name'     => 'Kouadio Jean',
            'email'    => 'manageryaconsulting.ci',
            'password' => Hash::make('password'),
            'role'     => 'project_manager',
            'theme'    => 'light',
            'email_verified_at' => now(),
        ]);

        // Collaborateur
        User::create([
            'name'     => 'Amenan Sophie',
            'email'    => 'staff@yaconsulting.ci',
            'password' => Hash::make('password'),
            'role'     => 'staff_member',
            'theme'    => 'dark',
            'email_verified_at' => now(),
        ]);
    }
}
