<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order matters: parents first, children after (FK constraints).
     *
     *   php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,        // staff accounts (password: password)
            CategorySeeder::class,    // menu categories
            MenuSeeder::class,        // menus        -> categories
            DiningTableSeeder::class, // restaurant tables
            OrderSeeder::class,       // orders, order_items, payments -> users, tables, menus
        ]);
    }
}
