<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'company.name', 'value' => 'Minha Empresa LTDA'],
            ['key' => 'company.email', 'value' => 'contato@example.com'],
            ['key' => 'company.phone', 'value' => '+55 11 99999-9999'],
            ['key' => 'company.address', 'value' => 'Rua Exemplo, 123 - São Paulo/SP'],
        ];
        foreach ($defaults as $item) {
            Setting::query()->firstOrCreate(['key' => $item['key']], ['value' => $item['value']]);
        }
    }
}
