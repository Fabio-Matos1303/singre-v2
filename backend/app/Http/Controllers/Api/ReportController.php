<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->endOfMonth()->toDateString()))->endOfDay();

        $base = ServiceOrder::query()->whereBetween('opened_at', [$from, $to]);
        $count = (clone $base)->count();
        $sum = (clone $base)->sum('total');

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'orders' => [
                'count' => (int) $count,
                'total' => (float) $sum,
            ],
            'by_status' => $byStatus,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/reports/top",
     *   tags={"Reports"},
     *   security={{"bearerAuth":{}}},
     *   summary="Top clientes/produtos/serviços",
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function top(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->endOfMonth()->toDateString()))->endOfDay();
        $limit = max(1, min(50, (int) $request->query('limit', 5)));

        // top clients by revenue
        $topClients = DB::table('service_orders')
            ->select('client_id', DB::raw('SUM(total) as total'))
            ->whereBetween('opened_at', [$from, $to])
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        // top products by line total
        $topProducts = DB::table('service_order_product as sop')
            ->join('service_orders as so', 'so.id', '=', 'sop.service_order_id')
            ->select('sop.product_id', DB::raw('SUM(sop.total) as total'), DB::raw('SUM(sop.quantity) as quantity'))
            ->whereBetween('so.opened_at', [$from, $to])
            ->groupBy('sop.product_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        // top services by line total
        $topServices = DB::table('service_order_service as sos')
            ->join('service_orders as so', 'so.id', '=', 'sos.service_order_id')
            ->select('sos.service_id', DB::raw('SUM(sos.total) as total'), DB::raw('SUM(sos.quantity) as quantity'))
            ->whereBetween('so.opened_at', [$from, $to])
            ->groupBy('sos.service_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return response()->json([
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'top_clients' => $topClients,
            'top_products' => $topProducts,
            'top_services' => $topServices,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/reports/timeseries",
     *   tags={"Reports"},
     *   security={{"bearerAuth":{}}},
     *   summary="Série temporal de pedidos e receita",
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="interval", in="query", @OA\Schema(type="string", enum={"day","week"})),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function timeseries(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->query('from', now()->copy()->subDays(29)->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->toDateString()))->endOfDay();
        $interval = strtolower($request->query('interval', 'day')) === 'week' ? 'week' : 'day';

        $format = $interval === 'week' ? '%x-%v' : '%Y-%m-%d';
        $label = $interval === 'week' ? 'year_week' : 'date';

        $rows = ServiceOrder::query()
            ->selectRaw("DATE_FORMAT(opened_at, ?) as bucket, COUNT(*) as count, SUM(total) as total", [$format])
            ->whereBetween('opened_at', [$from, $to])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(function ($r) use ($interval, $label) {
                return [
                    $label => $r->bucket,
                    'orders' => (int) $r->count,
                    'revenue' => (float) $r->total,
                ];
            });

        return response()->json([
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'interval' => $interval,
            'series' => $rows,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/reports/status-series",
     *   tags={"Reports"},
     *   security={{"bearerAuth":{}}},
     *   summary="Série temporal por status",
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="interval", in="query", @OA\Schema(type="string", enum={"day","week"})),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function statusSeries(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->query('from', now()->copy()->subDays(29)->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->toDateString()))->endOfDay();
        $interval = strtolower($request->query('interval', 'day')) === 'week' ? 'week' : 'day';

        $format = $interval === 'week' ? '%x-%v' : '%Y-%m-%d';

        $rows = ServiceOrder::query()
            ->selectRaw("DATE_FORMAT(opened_at, ?) as bucket, status, COUNT(*) as count", [$format])
            ->whereBetween('opened_at', [$from, $to])
            ->groupBy('bucket', 'status')
            ->orderBy('bucket')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $bucket = $row->bucket;
            if (!isset($grouped[$bucket])) { $grouped[$bucket] = []; }
            $grouped[$bucket][$row->status ?? 'unknown'] = (int) $row->count;
        }

        $series = [];
        foreach ($grouped as $bucket => $statuses) {
            $series[] = [
                'bucket' => $bucket,
                'statuses' => $statuses,
            ];
        }

        return response()->json([
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'interval' => $interval,
            'series' => $series,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/reports/summary",
     *   tags={"Reports"},
     *   security={{"bearerAuth":{}}},
     *   summary="Resumo de pedidos e receita por período",
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function kpis(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->endOfMonth()->toDateString()))->endOfDay();

        $base = ServiceOrder::query()->whereBetween('opened_at', [$from, $to]);
        $count = (clone $base)->count();
        $sum = (clone $base)->sum('total');
        $avg = $count > 0 ? $sum / $count : 0;

        return response()->json([
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'avg_order_value' => (float) round($avg, 2),
            'orders_count' => (int) $count,
            'revenue_total' => (float) $sum,
        ]);
    }
}
