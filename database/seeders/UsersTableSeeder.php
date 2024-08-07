<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'dandi',
                'email' => 'dandi@gmail.com',
                'password' => Hash::make('dandi123'),
                'roles' => 'admin', // Admin role
            ],
            [
                'name' => 'rizka',
                'email' => 'rizka@gmail.com',
                'password' => Hash::make('rizka123'),
                'roles' => 'user', // Regular user role
            ]
        ]);
    }
}
