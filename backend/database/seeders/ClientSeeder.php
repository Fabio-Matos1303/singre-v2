<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::query()->create([
            'name' => 'Cliente Exemplo',
            'email' => 'cliente@example.com',
            'phone' => '+55 11 99999-0000',
            'document' => '000.000.000-00',
            'address' => 'Rua Exemplo, 123',
        ]);
    }
}
