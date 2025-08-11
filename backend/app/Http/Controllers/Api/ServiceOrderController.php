<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\ServiceOrderCreated;

class ServiceOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *   path="/api/service-orders",
     *   tags={"ServiceOrders"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(): JsonResponse
    {
        $orders = ServiceOrder::with(['client'])->latest()->paginate(15);
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *   path="/api/service-orders",
     *   tags={"ServiceOrders"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'products' => ['array'],
            'products.*.id' => ['required','exists:products,id'],
            'products.*.quantity' => ['nullable','integer','min:1'],
            'services' => ['array'],
            'services.*.id' => ['required','exists:services,id'],
            'services.*.quantity' => ['nullable','integer','min:1'],
        ]);

        $order = ServiceOrder::create([
            'client_id' => $data['client_id'],
            'status' => $data['status'] ?? 'open',
            'notes' => $data['notes'] ?? null,
            'opened_at' => now(),
        ]);

        $total = 0;
        if (!empty($data['products'])) {
            $attach = [];
            foreach ($data['products'] as $p) {
                $qty = $p['quantity'] ?? 1;
                $unit = optional(\App\Models\Product::find($p['id']))->price ?? 0;
                $line = $unit * $qty;
                $total += $line;
                $attach[$p['id']] = ['quantity'=>$qty,'unit_price'=>$unit,'total'=>$line];
            }
            $order->products()->attach($attach);
        }
        if (!empty($data['services'])) {
            $attach = [];
            foreach ($data['services'] as $s) {
                $qty = $s['quantity'] ?? 1;
                $unit = optional(\App\Models\Service::find($s['id']))->price ?? 0;
                $line = $unit * $qty;
                $total += $line;
                $attach[$s['id']] = ['quantity'=>$qty,'unit_price'=>$unit,'total'=>$line];
            }
            $order->services()->attach($attach);
        }

        $order->update(['total' => $total]);

        $order->refresh()->load(['client','products','services']);
        // Send email to client (Mailpit in dev)
        if ($order->client?->email) {
            Mail::to($order->client->email)->send(new ServiceOrderCreated($order));
        }
        return response()->json($order, 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *   path="/api/service-orders/{id}",
     *   tags={"ServiceOrders"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(ServiceOrder $serviceOrder): JsonResponse
    {
        return response()->json($serviceOrder->load(['client','products','services']));
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *   path="/api/service-orders/{id}",
     *   tags={"ServiceOrders"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(Request $request, ServiceOrder $serviceOrder): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes','string','max:50'],
            'notes' => ['nullable','string'],
        ]);
        $serviceOrder->update($data);
        return response()->json($serviceOrder);
    }

    public function pdf(ServiceOrder $serviceOrder)
    {
        /**
         * @OA\Get(
         *   path="/api/service-orders/{id}/pdf",
         *   tags={"ServiceOrders"},
         *   security={{"bearerAuth":{}}},
         *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
         *   @OA\Response(response=200, description="PDF"),
         * )
         */
        $order = $serviceOrder->load(['client','products','services']);
        $html = view('pdf.service_order', compact('order'))->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="service_order_'.$order->id.'.pdf"',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *   path="/api/service-orders/{id}",
     *   tags={"ServiceOrders"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function destroy(ServiceOrder $serviceOrder): JsonResponse
    {
        $serviceOrder->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
