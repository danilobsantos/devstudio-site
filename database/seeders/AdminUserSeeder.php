<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@devstudio.com.br'],
            [
                'name' => 'Administrador DevStudio',
                'password' => Hash::make('DevStudio@Admin2026'),
                'role' => UserRole::Admin,
            ]
        );
    }
}
