<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrderType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceOrderTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceOrderType::query();
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
            ServiceOrderType::query()->update(['is_default' => false]);
        }
        $item = ServiceOrderType::create($data);
        return response()->json($item, 201);
    }

    public function show(ServiceOrderType $os_type): JsonResponse
    {
        return response()->json($os_type);
    }

    public function update(Request $request, ServiceOrderType $os_type): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'is_default' => ['sometimes','boolean'],
        ]);
        if (array_key_exists('is_default', $data) && $data['is_default']) {
            ServiceOrderType::query()->update(['is_default' => false]);
        }
        $os_type->update($data);
        return response()->json($os_type);
    }

    public function destroy(ServiceOrderType $os_type): JsonResponse
    {
        $os_type->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
