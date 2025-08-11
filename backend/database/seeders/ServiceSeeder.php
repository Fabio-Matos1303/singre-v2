<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::query()->create([
            'name' => 'Serviço Exemplo',
            'description' => 'Descrição do serviço exemplo',
            'price' => 149.90,
        ]);
    }
}
