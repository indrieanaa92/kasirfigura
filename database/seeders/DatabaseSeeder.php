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
            ['name' => 'Figura Minimalis Kayu Jati', 'size' => '4R (10x15 cm)', 'price_sales' => 28000, 'price_agen' => 31000, 'price_umum' => 35000, 'stock' => 25],
            ['name' => 'Figura Minimalis Hitam Doff', 'size' => '5R (13x18 cm)', 'price_sales' => 36000, 'price_agen' => 40000, 'price_umum' => 45000, 'stock' => 20],
            ['name' => 'Figura Classic Gold Ukiran', 'size' => '8R (20x25 cm)', 'price_sales' => 68000, 'price_agen' => 76000, 'price_umum' => 85000, 'stock' => 14],
            ['name' => 'Figura Scandinavian Natural', 'size' => '10R (25x30 cm)', 'price_sales' => 88000, 'price_agen' => 98000, 'price_umum' => 110000, 'stock' => 12],
            ['name' => 'Figura Akrilik Frameless', 'size' => 'A4 (21x30 cm)', 'price_sales' => 76000, 'price_agen' => 85000, 'price_umum' => 95000, 'stock' => 18],
            ['name' => 'Figura Box 3D Mahar / Kanvas', 'size' => 'A3 (30x42 cm)', 'price_sales' => 132000, 'price_agen' => 148000, 'price_umum' => 165000, 'stock' => 8],
            ['name' => 'Figura Silver Luxury Metal', 'size' => '12R (30x38 cm)', 'price_sales' => 116000, 'price_agen' => 130000, 'price_umum' => 145000, 'stock' => 10],
            ['name' => 'Figura Putih Glossy (Stok Habis Demo)', 'size' => '6R (15x20 cm)', 'price_sales' => 44000, 'price_agen' => 49000, 'price_umum' => 55000, 'stock' => 0],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['name' => $item['name'], 'size' => $item['size']],
                [
                    'price' => $item['price_umum'],
                    'price_sales' => $item['price_sales'],
                    'price_agen' => $item['price_agen'],
                    'price_umum' => $item['price_umum'],
                    'stock' => $item['stock'],
                ]
            );
        }
    }
}
