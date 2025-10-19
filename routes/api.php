<?php

use App\Domain\Appointments\Controllers\AppointmentController;
use App\Domain\Appointments\Controllers\ClientAppointmentController;
use App\Domain\Appointments\Controllers\ClientProfessionalController;
use App\Domain\Appointments\Controllers\EquipmentController;
use App\Domain\Appointments\Controllers\ProfessionalController;
use App\Domain\Appointments\Controllers\RoomController;
use App\Domain\Clients\Controllers\ClientController;
use App\Domain\Clients\Controllers\ClientAuthController;
use App\Domain\Inventory\Controllers\ProductController;
use App\Domain\Inventory\Controllers\StockMovementController;
use App\Domain\Sales\Controllers\ClientPackageController;
use App\Domain\Sales\Controllers\ClientSelfPackageController;
use App\Domain\Sales\Controllers\ClientSaleController;
use App\Domain\Sales\Controllers\SaleController;
use App\Domain\Services\Controllers\ServiceController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'timestamp' => now()->toIso8601String(),
]));

Route::post('/auth/login', [AuthController::class, 'login']);

Route::prefix('client')->group(function (): void {
    Route::post('auth/register', [ClientAuthController::class, 'register']);
    Route::post('auth/login', [ClientAuthController::class, 'login']);
    Route::post('auth/verify', [ClientAuthController::class, 'verify']);

    Route::middleware('auth:client')->group(function (): void {
        Route::post('auth/logout', [ClientAuthController::class, 'logout']);
        Route::get('auth/me', [ClientAuthController::class, 'me']);
        Route::put('profile', [ClientAuthController::class, 'update']);
        Route::delete('profile', [ClientAuthController::class, 'destroy']);
        Route::get('packages', [ClientSelfPackageController::class, 'index']);
        Route::get('packages/available', [ClientSelfPackageController::class, 'available']);
        Route::post('packages', [ClientSelfPackageController::class, 'subscribe']);
        Route::get('professionals', [ClientProfessionalController::class, 'index']);
        Route::get('appointments', [ClientAppointmentController::class, 'index']);
        Route::post('appointments', [ClientAppointmentController::class, 'store']);
        Route::post('appointments/{appointment}/reschedule', [ClientAppointmentController::class, 'reschedule']);
        Route::post('appointments/{appointment}/cancel', [ClientAppointmentController::class, 'cancel']);
        Route::get('sales', [ClientSaleController::class, 'index']);
        Route::get('sales/{sale}', [ClientSaleController::class, 'show']);
    });
});

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
