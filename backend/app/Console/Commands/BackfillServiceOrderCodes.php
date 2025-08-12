<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillServiceOrderCodes extends Command
{
    protected $signature = 'orders:backfill-codes {--dry-run}';
    protected $description = 'Backfill service_orders.code using legacy_number or by year sequence (N/YY)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $this->info('Backfilling service order codes...'.($dry ? ' (dry-run)' : ''));

        // 1) From legacy_number directly (normaliza e left-pad 4 dígitos antes da barra)
        $legacy = DB::table('service_orders')->whereNotNull('legacy_number')->count();
        $this->line("Orders with legacy_number: {$legacy}");
        $page = 0; $perPage = 5000; $updated = 0;
        do {
            $rows = DB::table('service_orders')
                ->select('id','legacy_number')
                ->whereNotNull('legacy_number')
                ->whereNull('code')
                ->orderBy('id')
                ->offset($page * $perPage)
                ->limit($perPage)
                ->get();
            foreach ($rows as $r) {
                $raw = trim(str_replace(' ', '', (string) $r->legacy_number));
                $code = $raw;
                if (preg_match('/^(\d+)[\/\\-](\d{2})$/', $raw, $m)) {
                    $num = str_pad($m[1], 4, '0', STR_PAD_LEFT);
                    $yy = $m[2];
                    $code = $num . '/' . $yy;
                }
                if (! $dry) {
                    DB::table('service_orders')->where('id', $r->id)->update(['code' => $code]);
                }
                $updated++;
            }
            $page++;
        } while ($rows->count() === $perPage);
        $this->line("Legacy-number based codes updated: {$updated}");

        // 2) For missing code, generate per-year sequence N/YY by opened_at
        $years = DB::table('service_orders')
            ->select(DB::raw('YEAR(opened_at) as y'), DB::raw('COUNT(*) as c'))
            ->whereNull('code')
            ->groupBy(DB::raw('YEAR(opened_at)'))
            ->pluck('y');

        foreach ($years as $year) {
            $yy = substr((string) $year, -2);
            $rows = DB::table('service_orders')
                ->select('id')
                ->whereNull('code')
                ->whereYear('opened_at', $year)
                ->orderBy('opened_at')
                ->orderBy('id')
                ->get();
            $seq = 0;
            foreach ($rows as $row) {
                $seq++;
                $code = str_pad((string) $seq, 4, '0', STR_PAD_LEFT) . '/' . $yy;
                if (! $dry) {
                    DB::table('service_orders')->where('id', $row->id)->update(['code' => $code]);
                }
            }
            $this->line("Year {$year}: assigned {$seq} codes.");
        }

        $remaining = DB::table('service_orders')->whereNull('code')->count();
        $this->info("Backfill done. Remaining without code: {$remaining}");
        return self::SUCCESS;
    }
}
