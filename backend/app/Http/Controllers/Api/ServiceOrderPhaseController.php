<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrderPhase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceOrderPhaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceOrderPhase::query();
        if ($q = trim((string) $request->query('q', ''))) {
            $query->where('name', 'like', "%{$q}%");
        }
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min(200, $perPage));
        $sort = in_array($request->query('sort'), ['name','is_default','points','created_at'], true) ? $request->query('sort') : 'name';
        $order = strtolower((string) $request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = $query->orderBy($sort, $order)->paginate($perPage);
        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'is_default' => ['sometimes','boolean'],
            'points' => ['sometimes','integer','min:0'],
            'generates_commission' => ['sometimes','boolean'],
        ]);
        if (!empty($data['is_default'])) {
            ServiceOrderPhase::query()->update(['is_default' => false]);
        }
        $item = ServiceOrderPhase::create($data);
        return response()->json($item, 201);
    }

    public function show(ServiceOrderPhase $os_phase): JsonResponse
    {
        return response()->json($os_phase);
    }

    public function update(Request $request, ServiceOrderPhase $os_phase): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'is_default' => ['sometimes','boolean'],
            'points' => ['sometimes','integer','min:0'],
            'generates_commission' => ['sometimes','boolean'],
        ]);
        if (array_key_exists('is_default', $data) && $data['is_default']) {
            ServiceOrderPhase::query()->update(['is_default' => false]);
        }
        $os_phase->update($data);
        return response()->json($os_phase);
    }

    public function destroy(ServiceOrderPhase $os_phase): JsonResponse
    {
        $os_phase->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
