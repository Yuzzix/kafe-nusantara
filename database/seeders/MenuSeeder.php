<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    /**
     * Menus grouped by category slug.
     * Format: [name, price (Rupiah), description, is_featured, is_available]
     */
    private const MENUS = [
        'makanan-utama' => [
            ['Nasi Goreng Spesial',        35000, 'Nasi goreng bumbu rempah dengan telur mata sapi, ayam suwir, dan kerupuk.', true],
            ['Mie Goreng Jawa',            30000, 'Mie goreng manis gurih khas Jawa dengan sayuran dan telur.'],
            ['Ayam Geprek Sambal Matah',   32000, 'Ayam crispy digeprek dengan sambal matah segar dan nasi putih.', true],
            ['Nasi Ayam Bakar Madu',       38000, 'Ayam bakar olesan madu, lalapan, sambal terasi, dan nasi hangat.'],
            ['Sate Ayam Madura',           35000, '10 tusuk sate ayam dengan bumbu kacang dan lontong.'],
            ['Beef Teriyaki Rice Bowl',    45000, 'Irisan daging sapi saus teriyaki di atas nasi Jepang dan telur onsen.'],
            ['Spaghetti Bolognese',        42000, 'Spaghetti dengan saus daging sapi cincang dan keju parmesan.'],
            // Sold out today, demonstrates is_available = false
            ['Nasi Rendang',               40000, 'Rendang daging sapi dimasak 6 jam dengan nasi dan sayur nangka.', false, false],
        ],
        'camilan' => [
            ['Kentang Goreng',             22000, 'French fries renyah dengan saus sambal dan mayones.'],
            ['Pisang Goreng Keju',         20000, 'Pisang kepok goreng dengan taburan keju dan susu kental manis.', true],
            ['Tahu Cabe Garam',            18000, 'Tahu goreng crispy tumis cabai, bawang putih, dan daun bawang.'],
            ['Cireng Rujak',               18000, 'Cireng kenyal dengan sambal rujak pedas manis.'],
            ['Chicken Wings BBQ',          30000, '6 potong sayap ayam saus barbekyu.'],
            ['Roti Bakar Cokelat Keju',    25000, 'Roti bakar tebal isi cokelat dan keju parut.'],
        ],
        'kopi' => [
            ['Espresso',                   18000, 'Single shot espresso dari biji arabika Gayo.'],
            ['Americano',                  22000, 'Espresso dengan air panas/dingin, ringan dan bold.'],
            ['Cappuccino',                 28000, 'Espresso, susu steamed, dan foam tebal.'],
            ['Caffe Latte',                28000, 'Espresso dengan susu steamed yang lembut.'],
            ['Kopi Susu Gula Aren',        25000, 'Signature! Espresso, susu segar, dan gula aren asli.', true],
            ['Vietnam Drip',               22000, 'Kopi robusta tetes dengan susu kental manis.'],
            ['Caramel Macchiato',          32000, 'Susu, vanila, espresso, dan saus karamel.'],
        ],
        'non-kopi' => [
            ['Matcha Latte',               30000, 'Matcha Jepang premium dengan susu segar.', true],
            ['Red Velvet Latte',           30000, 'Minuman red velvet creamy dengan susu.'],
            ['Chocolate',                  28000, 'Cokelat Belgia pekat, panas atau dingin.'],
            ['Taro Latte',                 28000, 'Talas ungu manis dengan susu segar.'],
        ],
        'teh-minuman-segar' => [
            ['Es Teh Manis',                8000, 'Teh melati manis dingin, klasik dan menyegarkan.'],
            ['Lemon Tea',                  15000, 'Teh dengan perasan lemon segar.'],
            ['Lychee Tea',                 22000, 'Teh leci dengan buah leci utuh.', true],
            ['Es Jeruk Peras',             15000, 'Jeruk peras segar, manis atau tanpa gula.'],
            ['Jus Alpukat',                22000, 'Jus alpukat mentega dengan cokelat kental manis.'],
            ['Air Mineral',                 6000, 'Air mineral botol 600 ml.'],
        ],
        'dessert' => [
            ['Brownies Ice Cream',         28000, 'Brownies hangat dengan satu scoop es krim vanila.', true],
            ['Banana Split',               30000, 'Pisang dengan tiga rasa es krim, saus cokelat, dan kacang.'],
            ['Pudding Cokelat Vla',        18000, 'Puding cokelat lembut dengan vla vanila.'],
            ['Croissant Butter',           22000, 'Croissant mentega renyah, dipanggang setiap pagi.'],
        ],
        'menu-musiman' => [
            ['Pumpkin Spice Latte',        35000, 'Latte labu dengan rempah kayu manis, edisi musiman.', false, false],
        ],
    ];

    public function run(): void
    {
        $categoryIds = Category::pluck('id', 'slug');

        foreach (self::MENUS as $categorySlug => $menus) {
            foreach ($menus as $menu) {
                [$name, $price, $description] = $menu;
                $slug = Str::slug($name);
                $bundledImage = "images/menus/{$slug}.webp";

                Menu::create([
                    'category_id' => $categoryIds[$categorySlug],
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'price' => $price,
                    'image' => file_exists(public_path($bundledImage)) ? $bundledImage : null,
                    'is_featured' => $menu[3] ?? false,
                    'is_available' => $menu[4] ?? true,
                ]);
            }
        }
    }
}
