<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $all = Setting::query()->orderBy('key')->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required','string','max:255'],
            'value' => ['nullable','string'],
        ]);
        Setting::setValue($data['key'], $data['value'] ?? null);
        return response()->json(['message'=>'Saved'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting): JsonResponse
    {
        return response()->json($setting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting): JsonResponse
    {
        $data = $request->validate([
            'value' => ['nullable','string'],
        ]);
        Setting::setValue($setting->key, $data['value'] ?? null);
        return response()->json(['message'=>'Updated']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting): JsonResponse
    {
        $setting->delete();
        return response()->json(['message'=>'Deleted']);
    }
}
