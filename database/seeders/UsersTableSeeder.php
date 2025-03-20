<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::first()) {
            return;
        }

        User::create([
            'name' => 'Admin',
            'email' => 'admin@tms.com',
            'password' => Hash::make('secret'),
            'email_verified_at' => now(),
        ]);
    }
}
