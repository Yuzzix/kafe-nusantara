<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan Utama',        'icon' => '🍛', 'description' => 'Hidangan utama yang mengenyangkan, dari nasi hingga pasta.'],
            ['name' => 'Camilan',              'icon' => '🍟', 'description' => 'Teman ngobrol dan nongkrong, cocok untuk dibagi bersama.'],
            ['name' => 'Kopi',                 'icon' => '☕', 'description' => 'Kopi pilihan dari biji arabika & robusta Nusantara.'],
            ['name' => 'Non-Kopi',             'icon' => '🧋', 'description' => 'Minuman susu dan latte tanpa kafein kopi.'],
            ['name' => 'Teh & Minuman Segar',  'icon' => '🍹', 'description' => 'Teh, jus, dan minuman dingin penghilang dahaga.'],
            ['name' => 'Dessert',              'icon' => '🍰', 'description' => 'Pencuci mulut manis untuk menutup santapan.'],
            // Inactive category: seasonal menu that is currently hidden from customers.
            ['name' => 'Menu Musiman',         'icon' => '🎃', 'description' => 'Menu spesial terbatas yang hanya tersedia pada musim tertentu.', 'is_active' => false],
        ];

        foreach ($categories as $index => $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'sort_order' => $index + 1,
                'is_active' => $category['is_active'] ?? true,
            ]);
        }
    }
}
