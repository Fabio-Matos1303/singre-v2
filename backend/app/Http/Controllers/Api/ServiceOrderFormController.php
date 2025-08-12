<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrderForm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceOrderFormController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceOrderForm::query();
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
            'is_warranty' => ['sometimes','boolean'],
            'generates_commission' => ['sometimes','boolean'],
            'require_equipment' => ['sometimes','boolean'],
        ]);
        if (!empty($data['is_default'])) {
            ServiceOrderForm::query()->update(['is_default' => false]);
        }
        $item = ServiceOrderForm::create($data);
        return response()->json($item, 201);
    }

    public function show(ServiceOrderForm $os_form): JsonResponse
    {
        return response()->json($os_form);
    }

    public function update(Request $request, ServiceOrderForm $os_form): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'is_default' => ['sometimes','boolean'],
            'is_warranty' => ['sometimes','boolean'],
            'generates_commission' => ['sometimes','boolean'],
            'require_equipment' => ['sometimes','boolean'],
        ]);
        if (array_key_exists('is_default', $data) && $data['is_default']) {
            ServiceOrderForm::query()->update(['is_default' => false]);
        }
        $os_form->update($data);
        return response()->json($os_form);
    }

    public function destroy(ServiceOrderForm $os_form): JsonResponse
    {
        $os_form->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
