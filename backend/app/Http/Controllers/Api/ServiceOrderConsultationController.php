<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrderConsultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceOrderConsultationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceOrderConsultation::query();
        if ($q = trim((string) $request->query('q', ''))) {
            $query->where('name', 'like', "%{$q}%");
        }
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min(200, $perPage));
        $sort = in_array($request->query('sort'), ['name','is_default','created_at'], true) ? $request->query('sort') : 'name';
        $order = strtolower((string) $request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = $query->orderBy($sort, $order)->paginate($perPage);
        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'is_default' => ['sometimes','boolean'],
        ]);
        if (!empty($data['is_default'])) {
            ServiceOrderConsultation::query()->update(['is_default' => false]);
        }
        $item = ServiceOrderConsultation::create($data);
        return response()->json($item, 201);
    }

    public function show(ServiceOrderConsultation $os_consultation): JsonResponse
    {
        return response()->json($os_consultation);
    }

    public function update(Request $request, ServiceOrderConsultation $os_consultation): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'is_default' => ['sometimes','boolean'],
        ]);
        if (array_key_exists('is_default', $data) && $data['is_default']) {
            ServiceOrderConsultation::query()->update(['is_default' => false]);
        }
        $os_consultation->update($data);
        return response()->json($os_consultation);
    }

    public function destroy(ServiceOrderConsultation $os_consultation): JsonResponse
    {
        $os_consultation->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
