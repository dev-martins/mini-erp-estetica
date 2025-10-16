<?php

use App\Domain\Appointments\Controllers\AppointmentController;
use App\Domain\Appointments\Controllers\EquipmentController;
use App\Domain\Appointments\Controllers\ProfessionalController;
use App\Domain\Appointments\Controllers\RoomController;
use App\Domain\Clients\Controllers\ClientController;
use App\Domain\Inventory\Controllers\ProductController;
use App\Domain\Inventory\Controllers\StockMovementController;
use App\Domain\Sales\Controllers\ClientPackageController;
use App\Domain\Sales\Controllers\SaleController;
use App\Domain\Services\Controllers\ServiceController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'timestamp' => now()->toIso8601String(),
]));

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::apiResource('clients', ClientController::class);
    Route::apiResource('services', ServiceController::class);
    Route::get('professionals', [ProfessionalController::class, 'index']);
    Route::get('rooms', [RoomController::class, 'index']);
    Route::get('equipments', [EquipmentController::class, 'index']);
    Route::apiResource('appointments', AppointmentController::class);
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'setStatus'])
        ->name('appointments.status');
    Route::get('clients/{client}/packages', [ClientPackageController::class, 'index']);

    Route::apiResource('products', ProductController::class);
    Route::get('stock-movements', [StockMovementController::class, 'index']);
    Route::post('stock-movements', [StockMovementController::class, 'store']);

    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show']);
});
