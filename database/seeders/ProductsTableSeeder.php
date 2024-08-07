<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            ['nama' => 'Smartphone', 'id_category' => 1, 'deskripsi' => 'Latest model smartphone', 'harga' => 299],
            ['nama' => 'Sofa', 'id_category' => 2, 'deskripsi' => 'Comfortable 3-seater sofa', 'harga' => 499],
            ['nama' => 'T-shirt', 'id_category' => 3, 'deskripsi' => 'Cotton T-shirt', 'harga' => 19],
            ['nama' => 'Novel', 'id_category' => 4, 'deskripsi' => 'Bestselling novel', 'harga' => 15],
            ['nama' => 'Action Figure', 'id_category' => 5, 'deskripsi' => 'Collectible action figure', 'harga' => 25],
        ]);
    }
}
