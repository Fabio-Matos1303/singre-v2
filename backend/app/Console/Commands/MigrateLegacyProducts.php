<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class MigrateLegacyProducts extends Command
{
    protected $signature = 'legacy:migrate:products {--chunk=1000}';
    protected $description = 'Migrate legacy products from starnew.cadastroproduto to v2 products';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $this->info('Starting products migration...');

        $legacy = DB::connection('legacy');
        $total = (int) $legacy->table('cadastroproduto')->count();
        $this->info("Legacy products found: {$total}");

        $processed = 0;
        $legacy->table('cadastroproduto')->orderBy('pro_codigo')->chunkById($chunk, function ($rows) use (&$processed) {
            foreach ($rows as $row) {
                $name = trim($row->pro_descricao ?? 'Sem Nome');
                $sku = (string) ($row->pro_codigo ?? '');
                $description = $row->pro_observacao ?? null;
                $price = $this->toDecimal($row->pro_precovenda ?? 0);
                $stock = (int) ($row->pro_saldoestoque ?? 0);

                $name = $this->convertToUtf8($name);
                $description = $this->convertToUtf8($description);

                // Upsert by sku if present, else by name
                $product = $sku ? Product::query()->firstOrNew(['sku' => $sku])
                                : Product::query()->firstOrNew(['name' => $name]);

                $product->fill([
                    'name' => $name,
                    'sku' => $sku,
                    'legacy_code' => $sku,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                ]);
                $product->save();
                $processed++;
            }
            $this->info("Processed: {$processed}");
        }, 'pro_codigo');

        $this->info('Products migration done.');
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
