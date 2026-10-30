<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Restaurant staff. Every account uses the password: password
     */
    public function run(): void
    {
        $staff = [
            ['name' => 'Admin Kafe',     'email' => 'admin@kafe.test',    'role' => User::ROLE_ADMIN,   'phone' => '081200000001'],
            ['name' => 'Siti Rahmawati', 'email' => 'kasir@kafe.test',    'role' => User::ROLE_CASHIER, 'phone' => '081200000002'],
            ['name' => 'Dewi Lestari',   'email' => 'kasir2@kafe.test',   'role' => User::ROLE_CASHIER, 'phone' => '081200000003'],
            ['name' => 'Budi Santoso',   'email' => 'pelayan@kafe.test',  'role' => User::ROLE_WAITER,  'phone' => '081200000004'],
            ['name' => 'Rizky Pratama',  'email' => 'pelayan2@kafe.test', 'role' => User::ROLE_WAITER,  'phone' => '081200000005'],
            ['name' => 'Andi Wijaya',    'email' => 'dapur@kafe.test',    'role' => User::ROLE_KITCHEN, 'phone' => '081200000006'],
        ];

        foreach ($staff as $person) {
            User::create([
                ...$person,
                'password' => 'password', // hashed automatically by the 'hashed' cast
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // One deactivated account, useful to show the is_active flag in the UI.
        User::create([
            'name' => 'Joko (Mantan Pelayan)',
            'email' => 'joko@kafe.test',
            'role' => User::ROLE_WAITER,
            'phone' => '081200000007',
            'password' => 'password',
            'is_active' => false,
        ]);
    }
}
