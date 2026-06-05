<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PropertyTypeSeeder::class,
            PropertyFeatureSeeder::class,
            BusinessTypeSeeder::class,
            SettingSeeder::class,
            PageSeeder::class,
            MenuItemSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@exemplo.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'admin_enabled' => true,
                'permissions' => null,
            ]
        );
    }
}
