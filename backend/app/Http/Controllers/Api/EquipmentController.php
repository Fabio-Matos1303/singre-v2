<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * List equipment with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Equipment::query()->with(['client']);

        if ($clientId = $request->query('client_id')) {
            $query->where('client_id', $clientId);
        }
        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(function ($qb) use ($q) {
                $qb->where('serial_company', 'like', "%{$q}%")
                   ->orWhere('serial_manufacturer', 'like', "%{$q}%")
                   ->orWhere('brand', 'like', "%{$q}%")
                   ->orWhere('model', 'like', "%{$q}%");
            });
        }

        // Sorting
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $sortable = ['id','serial_company','serial_manufacturer','brand','model','intervention_count','created_at'];
        $defaultSort = 'id';
        $sort = (string) $request->query('sort', $defaultSort);
        $order = strtolower((string) $request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (!in_array($sort, $sortable, true)) { $sort = $defaultSort; }
        $items = $query->orderBy($sort, $order)->orderBy('id', 'desc')->paginate($perPage);
        return response()->json($items);
    }

    /**
     * Quick lookup by serial (empresa/fabricante), optional by client.
     */
    public function lookup(Request $request): JsonResponse
    {
        $serial = trim((string) $request->query('serial', ''));
        if ($serial === '') {
            return response()->json(['message' => 'Informe o parâmetro serial.'], 422);
        }
        $query = Equipment::query()->with('client')
            ->where(function ($q) use ($serial) {
                $q->where('serial_company', 'like', "%{$serial}%")
                  ->orWhere('serial_manufacturer', 'like', "%{$serial}%");
            })
            ->orderByDesc('id')
            ->limit((int) $request->query('limit', 10));
        if ($clientId = $request->query('client_id')) {
            $query->where('client_id', $clientId);
        }
        return response()->json(['data' => $query->get()]);
    }

    /**
     * Create new equipment.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['required','exists:clients,id'],
            'serial_company' => ['nullable','string','max:255','unique:equipment,serial_company'],
            'serial_manufacturer' => ['nullable','string','max:255','unique:equipment,serial_manufacturer'],
            'brand' => ['nullable','string','max:255'],
            'model' => ['nullable','string','max:255'],
            'configuration' => ['nullable','string'],
        ]);
        if (empty($data['serial_company']) && empty($data['serial_manufacturer'])) {
            return response()->json(['message' => 'Informe série da empresa ou do fabricante.'], 422);
        }
        $equipment = Equipment::create($data);
        return response()->json($equipment->fresh()->load('client'), 201);
    }

    /**
     * Show equipment.
     */
    public function show(Equipment $equipment): JsonResponse
    {
        return response()->json($equipment->load('client'));
    }

    /**
     * Update equipment.
     */
    public function update(Request $request, Equipment $equipment): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['sometimes','exists:clients,id'],
            'serial_company' => ['nullable','string','max:255','unique:equipment,serial_company,'.$equipment->id],
            'serial_manufacturer' => ['nullable','string','max:255','unique:equipment,serial_manufacturer,'.$equipment->id],
            'brand' => ['nullable','string','max:255'],
            'model' => ['nullable','string','max:255'],
            'configuration' => ['nullable','string'],
        ]);
        $equipment->update($data);
        return response()->json($equipment->fresh()->load('client'));
    }

    /**
     * Delete equipment.
     */
    public function destroy(Equipment $equipment): JsonResponse
    {
        $equipment->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
