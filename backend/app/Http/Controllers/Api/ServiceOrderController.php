<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\ServiceOrderCreated;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceOrderStatusHistory;
use App\Models\Equipment;
use App\Models\Setting;
use App\Models\ServiceOrderType;
use App\Models\ServiceOrderForm;
use App\Models\ServiceOrderPhase;
use App\Models\ServiceOrderConsultation;

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
    public function index(Request $request): JsonResponse
    {
        $query = ServiceOrder::query()->with(['client','equipment','type','form','phase','consultation']);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $status = (string) $request->query('status', '');
        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($equipmentId = (int) $request->query('equipment_id', 0)) {
            $query->where('equipment_id', $equipmentId);
        }

        $from = $request->query('from');
        $to = $request->query('to');
        if ($from) {
            $query->whereDate('opened_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('opened_at', '<=', $to);
        }

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        // Sorting
        $sortable = ['id','code','status','total','opened_at','closed_at','created_at'];
        $sort = (string) $request->query('sort', 'sequence');
        $order = strtolower((string) $request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if ($sort === 'sequence') {
            $query->orderBy('sequence_year', $order)->orderBy('sequence_number', $order)->orderBy('id', 'desc');
        } elseif (in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $order)->orderBy('id', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate($perPage);
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
            'equipment_id' => ['nullable','exists:equipment,id'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'type_id' => ['nullable','exists:service_order_types,id'],
            'form_id' => ['nullable','exists:service_order_forms,id'],
            'phase_id' => ['nullable','exists:service_order_phases,id'],
            'consultation_id' => ['nullable','exists:service_order_consultations,id'],
            'products' => ['array'],
            'products.*.id' => ['required','exists:products,id'],
            'products.*.quantity' => ['nullable','integer','min:1'],
            'services' => ['array'],
            'services.*.id' => ['required','exists:services,id'],
            'services.*.quantity' => ['nullable','integer','min:1'],
        ]);

        // Determine defaults
        $defaultTypeId = ServiceOrderType::query()->where('is_default', true)->value('id');
        $defaultFormId = ServiceOrderForm::query()->where('is_default', true)->value('id');
        $defaultPhaseId = ServiceOrderPhase::query()->where('is_default', true)->value('id');
        $defaultConsultationId = ServiceOrderConsultation::query()->where('is_default', true)->value('id');

        $selectedFormId = $data['form_id'] ?? $defaultFormId;
        if ($selectedFormId) {
            $formRequiresEquipment = (bool) ServiceOrderForm::query()->where('id', $selectedFormId)->value('require_equipment');
            if ($formRequiresEquipment && empty($data['equipment_id'])) {
                return response()->json(['message' => 'Equipamento é obrigatório para a forma selecionada.','errors'=>['equipment_id'=>['Equipamento obrigatório.']]], 422);
            }
        }

        $order = ServiceOrder::create([
            'client_id' => $data['client_id'],
            'equipment_id' => $data['equipment_id'] ?? null,
            'status' => $data['status'] ?? 'open',
            'notes' => $data['notes'] ?? null,
            'opened_at' => now(),
            'type_id' => $data['type_id'] ?? $defaultTypeId,
            'form_id' => $selectedFormId,
            'phase_id' => $data['phase_id'] ?? $defaultPhaseId,
            'consultation_id' => $data['consultation_id'] ?? $defaultConsultationId,
        ]);

        // Numeração N/YY: gera e preenche code/sequence_number/sequence_year
        $yearTwoDigits = (int) now()->format('y');
        $currentYear = (int) now()->format('Y');
        $seqSettingKey = "os.sequence.{$currentYear}";
        $next = Setting::intValue($seqSettingKey, 0) + 1;
        Setting::setValue($seqSettingKey, (string) $next);
        $code = $next . '/' . str_pad((string) $yearTwoDigits, 2, '0', STR_PAD_LEFT);
        $order->update([
            'code' => $code,
            'sequence_year' => $currentYear,
            'sequence_number' => $next,
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

        // Incrementa contador de intervenções do equipamento, se houver
        if ($order->equipment_id) {
            Equipment::where('id', $order->equipment_id)->increment('intervention_count');
        }

        $order->refresh()->load(['client','equipment','type','form','phase','consultation','products','services']);
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
        return response()->json($serviceOrder->load(['client','equipment','type','form','phase','consultation','products','services','statusHistories']));
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
            'equipment_id' => ['sometimes','nullable','exists:equipment,id'],
            'type_id' => ['sometimes','nullable','exists:service_order_types,id'],
            'form_id' => ['sometimes','nullable','exists:service_order_forms,id'],
            'phase_id' => ['sometimes','nullable','exists:service_order_phases,id'],
            'consultation_id' => ['sometimes','nullable','exists:service_order_consultations,id'],
            'products' => ['sometimes','array'],
            'products.*.id' => ['required_with:products','exists:products,id'],
            'products.*.quantity' => ['nullable','integer','min:1'],
            'services' => ['sometimes','array'],
            'services.*.id' => ['required_with:services','exists:services,id'],
            'services.*.quantity' => ['nullable','integer','min:1'],
        ]);

        return DB::transaction(function () use ($data, $serviceOrder) {
            $updates = [];
            $originalEquipmentId = $serviceOrder->equipment_id;
            if (array_key_exists('status', $data)) {
                // Update closed_at when status changes to closed
                if ($data['status'] === 'closed') {
                    $updates['closed_at'] = now();
                } else {
                    $updates['closed_at'] = null;
                }
                $updates['status'] = $data['status'];
            }
            if (array_key_exists('notes', $data)) {
                $updates['notes'] = $data['notes'];
            }
            if (array_key_exists('equipment_id', $data)) {
                $updates['equipment_id'] = $data['equipment_id'];
            }
            if (array_key_exists('type_id', $data)) { $updates['type_id'] = $data['type_id']; }
            if (array_key_exists('form_id', $data)) { $updates['form_id'] = $data['form_id']; }
            if (array_key_exists('phase_id', $data)) { $updates['phase_id'] = $data['phase_id']; }
            if (array_key_exists('consultation_id', $data)) { $updates['consultation_id'] = $data['consultation_id']; }

            // Pre-validate form/equipment rules
            $newFormId = array_key_exists('form_id', $data) ? $data['form_id'] : $serviceOrder->form_id;
            $newEquipmentId = array_key_exists('equipment_id', $data) ? $data['equipment_id'] : $serviceOrder->equipment_id;
            if ($newFormId) {
                $requires = (bool) ServiceOrderForm::query()->where('id', $newFormId)->value('require_equipment');
                if ($requires && empty($newEquipmentId)) {
                    abort(response()->json(['message'=>'Equipamento é obrigatório para a forma selecionada.','errors'=>['equipment_id'=>['Equipamento obrigatório.']]], 422));
                }
            }

            if (!empty($updates)) {
                $originalStatus = $serviceOrder->status;
                $serviceOrder->update($updates);
                if (array_key_exists('status', $updates) && $originalStatus !== $updates['status']) {
                    ServiceOrderStatusHistory::create([
                        'service_order_id' => $serviceOrder->id,
                        'user_id' => optional(auth()->user())->id,
                        'from_status' => $originalStatus,
                        'to_status' => $updates['status'],
                        'changed_at' => now(),
                    ]);
                }
                if (array_key_exists('equipment_id', $updates) && $updates['equipment_id'] && $updates['equipment_id'] !== $originalEquipmentId) {
                    Equipment::where('id', $updates['equipment_id'])->increment('intervention_count');
                }
            }

            $recalculateTotal = false;

            if (array_key_exists('products', $data)) {
                $attach = [];
                foreach ($data['products'] as $p) {
                    $qty = $p['quantity'] ?? 1;
                    $unit = optional(Product::find($p['id']))->price ?? 0;
                    $line = $unit * $qty;
                    $attach[$p['id']] = ['quantity'=>$qty,'unit_price'=>$unit,'total'=>$line];
                }
                $serviceOrder->products()->sync($attach);
                $recalculateTotal = true;
            }

            if (array_key_exists('services', $data)) {
                $attach = [];
                foreach ($data['services'] as $s) {
                    $qty = $s['quantity'] ?? 1;
                    $unit = optional(Service::find($s['id']))->price ?? 0;
                    $line = $unit * $qty;
                    $attach[$s['id']] = ['quantity'=>$qty,'unit_price'=>$unit,'total'=>$line];
                }
                $serviceOrder->services()->sync($attach);
                $recalculateTotal = true;
            }

            if ($recalculateTotal) {
                // Recalculate from pivots to avoid rounding divergence
                $serviceOrder->load(['products','services']);
                $total = 0;
                foreach ($serviceOrder->products as $p) {
                    $total += (float) ($p->pivot->total ?? 0);
                }
                foreach ($serviceOrder->services as $s) {
                    $total += (float) ($s->pivot->total ?? 0);
                }
                $serviceOrder->update(['total' => $total]);
            }

            return response()->json($serviceOrder->fresh()->load(['client','equipment','type','form','phase','consultation','products','services','statusHistories']));
        });
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
