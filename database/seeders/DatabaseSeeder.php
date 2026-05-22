<?php

namespace Database\Seeders;

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


        \App\Models\User::updateOrCreate(
            ['email' => 'onemaxweb@gmail.com'],
            [
                'name' => 'admin',
                'role_type' => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'manager@manager.com'],
            [
                'name' => 'manager',
                'role_type' => 'Manager',
                'password' => Hash::make('12345678'),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'cashier@cashier.com'],
            [
                'name' => 'cashier',
                'role_type' => 'Cashier',
                'password' => Hash::make('12345678'),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'waiter@waiter.com'],
            [
                'name' => 'waiter',
                'role_type' => 'Waiter',
                'password' => Hash::make('12345678'),
            ]
        );

    }
}
