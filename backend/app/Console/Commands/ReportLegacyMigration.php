<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReportLegacyMigration extends Command
{
    protected $signature = 'legacy:report';
    protected $description = 'Generate migration coverage and anomalies report';

    public function handle(): int
    {
        $this->info('Generating migration report...');

        $ordersCount = DB::table('service_orders')->count();
        $ordersWithItems = DB::table('service_orders as so')
            ->leftJoin('service_order_product as sop', 'sop.service_order_id', '=', 'so.id')
            ->leftJoin('service_order_service as sos', 'sos.service_order_id', '=', 'so.id')
            ->select('so.id')
            ->where(function ($q) {
                $q->whereNotNull('sop.id')->orWhereNotNull('sos.id');
            })
            ->distinct()->count('so.id');
        $ordersWithoutItems = $ordersCount - $ordersWithItems;

        $itemsMissingProduct = DB::table('service_order_product as sop')
            ->leftJoin('products as p', 'p.id', '=', 'sop.product_id')
            ->whereNull('p.id')
            ->count();
        $itemsMissingService = DB::table('service_order_service as sos')
            ->leftJoin('services as s', 's.id', '=', 'sos.service_id')
            ->whereNull('s.id')
            ->count();

        $totalsZero = DB::table('service_orders')->where('total', 0)->count();

        $this->line("Total OS: {$ordersCount}");
        $this->line("OS com itens: {$ordersWithItems}");
        $this->line("OS sem itens: {$ordersWithoutItems}");
        $this->line("Itens de produto sem match: {$itemsMissingProduct}");
        $this->line("Itens de serviço sem match: {$itemsMissingService}");
        $this->line("OS com total = 0: {$totalsZero}");

        // Amostra de OS sem itens
        $sample = DB::table('service_orders as so')
            ->leftJoin('service_order_product as sop', 'sop.service_order_id', '=', 'so.id')
            ->leftJoin('service_order_service as sos', 'sos.service_order_id', '=', 'so.id')
            ->whereNull('sop.id')->whereNull('sos.id')
            ->select('so.id', 'so.legacy_number', 'so.status', 'so.opened_at', 'so.closed_at')
            ->limit(10)->get();
        if ($sample->count()) {
            $this->line("Amostra de OS sem itens (10):");
            foreach ($sample as $row) {
                $this->line(json_encode($row));
            }
        }

        $this->info('Report done.');
        return self::SUCCESS;
    }
}
