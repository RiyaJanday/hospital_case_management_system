<?php

namespace Database\Seeders;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Demo Viewer',
            'email' => 'viewer@gmail.com',
            'password' => Hash::make('Viewer@123'),
            'role' => 'viewer',
        ]);

        MedicalCase::factory()->count(25)->create([
            'created_by' => $admin->id,
        ]);
    }
}
