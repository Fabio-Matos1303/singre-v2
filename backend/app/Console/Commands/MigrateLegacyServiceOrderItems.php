<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyServiceOrderItems extends Command
{
    protected $signature = 'legacy:migrate:service-order-items {--chunk=20000}';
    protected $description = 'Migrate legacy service order items (orcamentoordem_*) into v2 pivot tables';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $this->info('Starting service order items migration...');

        $legacy = DB::connection('legacy');

        $migrate = function ($table) use ($legacy, $chunk) {
            $processed = 0;
            $legacy->table($table)->orderBy('orc_ordemservico')->chunk($chunk, function ($rows) use (&$processed) {
                foreach ($rows as $row) {
                    $orderId = DB::table('service_orders')->where('legacy_number', (string) $row->orc_ordemservico)->value('id');
                    if (! $orderId) { continue; }

                    $qty = (float) ($row->orc_quantidade ?? 1);
                    $unit = (string) $row->orc_valor;
                    $total = number_format($qty * (float) str_replace(',', '.', (string) $row->orc_valor), 2, '.', '');
                    $type = strtolower((string) $row->orc_tipo);

                    if ($type === 'produto' || $type === 'prod' || $type === 'p') {
                        $productId = DB::table('products')->where('legacy_code', (string) $row->orc_codigo)->orWhere('sku', (string) $row->orc_codigo)->value('id');
                        if (! $productId) { continue; }
                        DB::table('service_order_product')->insert([
                            'service_order_id' => $orderId,
                            'product_id' => $productId,
                            'quantity' => max(1, (int) round($qty)),
                            'unit_price' => $unit,
                            'total' => $total,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else { // serviço
                        $serviceId = DB::table('services')->where('legacy_code', (string) $row->orc_codigo)->value('id');
                        if (! $serviceId) { continue; }
                        DB::table('service_order_service')->insert([
                            'service_order_id' => $orderId,
                            'service_id' => $serviceId,
                            'quantity' => max(1, (int) round($qty)),
                            'unit_price' => $unit,
                            'total' => $total,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    $processed++;
                }
                $this->info("Processed items from chunk, total so far: {$processed}");
            });
        };
        $migrate('orcamentoordem_ativa');
        $migrate('orcamentoordem_concluida');
        $this->info('Service order items migration done.');

        return self::SUCCESS;
    }
}
