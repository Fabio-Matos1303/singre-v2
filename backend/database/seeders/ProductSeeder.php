<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::query()->create([
            'name' => 'Produto Exemplo',
            'sku' => 'SKU-001',
            'description' => 'Descrição do produto exemplo',
            'price' => 199.90,
            'stock' => 10,
        ]);
    }
}
