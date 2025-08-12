<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Service;

class MigrateLegacyServices extends Command
{
    protected $signature = 'legacy:migrate:services {--chunk=1000}';
    protected $description = 'Migrate legacy services from starnew.cadastroservico to v2 services';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $this->info('Starting services migration...');

        $legacy = DB::connection('legacy');
        $total = (int) $legacy->table('cadastroservico')->count();
        $this->info("Legacy services found: {$total}");

        $processed = 0;
        $legacy->table('cadastroservico')->orderBy('pro_codigo')->chunkById($chunk, function ($rows) use (&$processed) {
            foreach ($rows as $row) {
                $name = trim($row->pro_descricao ?? 'Sem Nome');
                $description = $row->pro_observacao ?? null;
                $price = $this->toDecimal($row->pro_precovenda ?? 0);

                $name = $this->convertToUtf8($name);
                $description = $this->convertToUtf8($description);

                $service = Service::query()->firstOrNew(['name' => $name]);
                $service->fill([
                    'name' => $name,
                    'legacy_code' => $row->pro_codigo ?? null,
                    'description' => $description,
                    'price' => $price,
                ]);
                $service->save();
                $processed++;
            }
            $this->info("Processed: {$processed}");
        }, 'pro_codigo');

        $this->info('Services migration done.');
        return self::SUCCESS;
    }

    private function toDecimal($value): string
    {
        if ($value === null) return '0.00';
        $str = (string) $value;
        $str = str_replace(['.', ' '], ['', ''], $str);
        $str = str_replace(',', '.', $str);
        return number_format((float) $str, 2, '.', '');
    }

    private function convertToUtf8(?string $value): ?string
    {
        if ($value === null) return null;
        $looksUtf8 = mb_check_encoding($value, 'UTF-8');
        return $looksUtf8 ? $value : mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
    }
}
