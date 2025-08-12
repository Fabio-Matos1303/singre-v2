<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateServiceOrderTotals extends Command
{
    protected $signature = 'orders:recalc-totals';
    protected $description = 'Recalculate service_orders.total from pivot tables';

    public function handle(): int
    {
        $this->info('Recalculating totals...');
        // Build a temp sums table using union of pivots
        $sums = DB::table('service_order_product')
            ->select('service_order_id', DB::raw('SUM(total) as sum_total'))
            ->groupBy('service_order_id');

        $rows = DB::table('service_order_service')
            ->select('service_order_id', DB::raw('SUM(total) as sum_total'))
            ->groupBy('service_order_id');

        $union = DB::query()->fromSub(
            DB::query()->fromSub($sums, 'p')
                ->unionAll($rows),
            'u'
        )->select('service_order_id', DB::raw('SUM(sum_total) as sum_total'))
         ->groupBy('service_order_id');

        // Update join
        $updated = DB::table('service_orders as so')
            ->leftJoinSub($union, 'x', 'x.service_order_id', '=', 'so.id')
            ->update(['so.total' => DB::raw('COALESCE(x.sum_total, 0)')]);

        $this->info("Totals updated for {$updated} rows.");

        // Quick summary
        $zero = DB::table('service_orders')->where('total', 0)->count();
        $this->info("Orders with total = 0 after recalc: {$zero}");
        return self::SUCCESS;
    }
}
