<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceOrderController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ReportController;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Clientes
    Route::apiResource('clients', ClientController::class);
    // Produtos
    Route::apiResource('products', ProductController::class)->middleware('throttle:60,1');
    // Serviços
    Route::apiResource('services', ServiceController::class)->middleware('throttle:60,1');
    // Ordens de Serviço
    Route::apiResource('service-orders', ServiceOrderController::class);
    Route::get('service-orders/{service_order}/pdf', [ServiceOrderController::class, 'pdf']);
    Route::apiResource('settings', SettingController::class);
    Route::get('reports/summary', [ReportController::class, 'summary'])->middleware('throttle:20,1');
    Route::get('reports/top', [ReportController::class, 'top'])->middleware('throttle:20,1');
    Route::get('reports/timeseries', [ReportController::class, 'timeseries'])->middleware('throttle:20,1');
    Route::get('reports/status-series', [ReportController::class, 'statusSeries'])->middleware('throttle:20,1');
    Route::get('reports/kpis', [ReportController::class, 'kpis'])->middleware('throttle:20,1');
});
