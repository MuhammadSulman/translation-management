<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                UsersTableSeeder::class,
                LanguageSeeder::class,
                TagSeeder::class,
                TranslationsTableSeeder::class,
            ]
        );
    }
}
