<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with default users and sample frame products.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@kasirfigura.test'],
            [
                'name' => 'Admin Galeri Figura',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@kasirfigura.test'],
            [
                'name' => 'Kasir Utama',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KASIR,
            ]
        );

        $products = [
            ['name' => 'Figura Minimalis Kayu Jati', 'size' => '4R (10x15 cm)', 'price' => 35000, 'stock' => 25],
            ['name' => 'Figura Minimalis Hitam Doff', 'size' => '5R (13x18 cm)', 'price' => 45000, 'stock' => 20],
            ['name' => 'Figura Classic Gold Ukiran', 'size' => '8R (20x25 cm)', 'price' => 85000, 'stock' => 14],
            ['name' => 'Figura Scandinavian Natural', 'size' => '10R (25x30 cm)', 'price' => 110000, 'stock' => 12],
            ['name' => 'Figura Akrilik Frameless', 'size' => 'A4 (21x30 cm)', 'price' => 95000, 'stock' => 18],
            ['name' => 'Figura Box 3D Mahar / Kanvas', 'size' => 'A3 (30x42 cm)', 'price' => 165000, 'stock' => 8],
            ['name' => 'Figura Silver Luxury Metal', 'size' => '12R (30x38 cm)', 'price' => 145000, 'stock' => 10],
            ['name' => 'Figura Putih Glossy (Stok Habis Demo)', 'size' => '6R (15x20 cm)', 'price' => 55000, 'stock' => 0],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['name' => $item['name'], 'size' => $item['size']],
                ['price' => $item['price'], 'stock' => $item['stock']]
            );
        }
    }
}
