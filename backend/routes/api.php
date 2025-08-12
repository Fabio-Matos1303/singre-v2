<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceOrderController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\ServiceOrderTypeController;
use App\Http\Controllers\Api\ServiceOrderFormController;
use App\Http\Controllers\Api\ServiceOrderPhaseController;
use App\Http\Controllers\Api\ServiceOrderConsultationController;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Clientes
    Route::apiResource('clients', ClientController::class);
    Route::post('clients/{client}/restore', [ClientController::class, 'restore']);
    // Produtos
    Route::apiResource('products', ProductController::class)->middleware('throttle:60,1');
    Route::post('products/{product}/restore', [ProductController::class, 'restore']);
    // Serviços
    Route::apiResource('services', ServiceController::class)->middleware('throttle:60,1');
    Route::post('services/{service}/restore', [ServiceController::class, 'restore']);
    // Ordens de Serviço
    Route::apiResource('service-orders', ServiceOrderController::class);
    Route::get('service-orders/{service_order}/pdf', [ServiceOrderController::class, 'pdf']);
    // Equipamentos
    Route::get('equipment/lookup', [EquipmentController::class, 'lookup'])->middleware('throttle:60,1');
    Route::apiResource('equipment', EquipmentController::class)->middleware('throttle:60,1');
    // Parâmetros OS
    Route::apiResource('os-types', ServiceOrderTypeController::class)->middleware('throttle:60,1');
    Route::apiResource('os-forms', ServiceOrderFormController::class)->middleware('throttle:60,1');
    Route::apiResource('os-phases', ServiceOrderPhaseController::class)->middleware('throttle:60,1');
    Route::apiResource('os-consultations', ServiceOrderConsultationController::class)->middleware('throttle:60,1');
    Route::apiResource('settings', SettingController::class);
    Route::post('settings/reset-os-sequence', [SettingController::class, 'resetOsSequence'])->middleware('throttle:5,1');
    Route::get('reports/summary', [ReportController::class, 'summary'])->middleware('throttle:20,1');
    Route::get('reports/top', [ReportController::class, 'top'])->middleware('throttle:20,1');
    Route::get('reports/timeseries', [ReportController::class, 'timeseries'])->middleware('throttle:20,1');
    Route::get('reports/status-series', [ReportController::class, 'statusSeries'])->middleware('throttle:20,1');
    Route::get('reports/kpis', [ReportController::class, 'kpis'])->middleware('throttle:20,1');

    // Backups
    Route::post('backups/run', [BackupController::class, 'run'])->middleware('throttle:5,1');
    Route::get('backups', [BackupController::class, 'index'])->middleware('throttle:10,1');
    Route::get('backups/{filename}/download', [BackupController::class, 'download'])->where('filename', '.*')->middleware('throttle:10,1');
});
