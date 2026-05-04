<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@pardal.com'],
            [
                'name'     => 'Admin Pardal',
                'password' => Hash::make('123456'),
            ]
        );

        $admin->assignRole('admin');

        $customer = User::firstOrCreate(
            ['email' => 'cliente@pardal.com'],
            [
                'name'     => 'Cliente Teste',
                'password' => Hash::make('123456'),
            ]
        );

        $customer->assignRole('customer');
    }
}
