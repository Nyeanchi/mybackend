<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\MaintenanceRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LocationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentMethodController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes || all good here
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Location routes (public for registration) || all good here
Route::prefix('locations')->group(function () {
    Route::get('regions', [LocationController::class, 'regions']);
    Route::get('regions/{region}/cities', [LocationController::class, 'cities']);
    Route::get('cities', [LocationController::class, 'allCities']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes || all good here
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::post('refresh', [AuthController::class, 'refreshToken']);
    });

    // Dashboard routes  || Working perfectly
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'stats']);
        Route::get('recent-activities', [DashboardController::class, 'recentActivities']);
        Route::get('analytics', [DashboardController::class, 'analytics']);
    });

    // User management routes || working perfectly, dont know about activate, landlords, tenants
    Route::apiResource('users', UserController::class);
    Route::prefix('users')->group(function () {
        Route::post('{user}/activate', [UserController::class, 'activate']);
        Route::get('landlords/list', [UserController::class, 'landlords']);
        Route::get('tenants/list', [UserController::class, 'tenants']);
    });

    // Property management routes || all good here except , i dont knows tatistics
    Route::apiResource('properties', PropertyController::class);
    Route::prefix('properties')->group(function () {
        Route::get('available/list', [PropertyController::class, 'available']);
        Route::get('{property}/statistics', [PropertyController::class, 'statistics']);
    });

    // Tenant management routes || get tenants good, i dont know about expiring-leases, payment-history, statistics not working
    Route::apiResource('tenants', TenantController::class);
    Route::prefix('tenants')->group(function () {
        Route::get('expiring-leases', [TenantController::class, 'expiringLeases']);
        Route::get('{tenant}/payment-history', [TenantController::class, 'paymentHistory']);
        Route::get('{tenant}/statistics', [TenantController::class, 'statistics']);
    });

    // Payment management routes  || working perfectly, dont know about markAsPaid
    Route::apiResource('payments', PaymentController::class);
    Route::prefix('payments')->group(function () {
        Route::get('pending/list', [PaymentController::class, 'pending']);
        Route::get('overdue/list', [PaymentController::class, 'overdue']);
        Route::get('statistics/summary', [PaymentController::class, 'statistics']);
        Route::post('{payment}/mark-paid', [PaymentController::class, 'markAsPaid']);
    });

    // Maintenance request routes ||working perfectly i dont know about assign, complete
    Route::apiResource('maintenance-requests', MaintenanceRequestController::class);
    Route::prefix('maintenance-requests')->group(function () {
        Route::get('pending/list', [MaintenanceRequestController::class, 'pending']);
        Route::get('statistics/summary', [MaintenanceRequestController::class, 'statistics']);
        Route::post('{maintenanceRequest}/assign', [MaintenanceRequestController::class, 'assign']);
        Route::post('{maintenanceRequest}/complete', [MaintenanceRequestController::class, 'complete']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread', [NotificationController::class, 'unread']);
        Route::post('{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);
    });

    // Settings routes || working per
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::post('/', [SettingsController::class, 'update']);
        Route::get('payment-methods', [SettingsController::class, 'paymentMethods']);
        Route::post('change-password', [SettingsController::class, 'changePassword']);
    });

    // Admin-only routes || all working ...
    Route::middleware('role:admin')->group(function () {

        // System settings || all working perfectly
        Route::prefix('admin/settings')->group(function () {
            Route::get('system', [SettingsController::class, 'systemSettings']);
            Route::post('system', [SettingsController::class, 'updateSystemSettings']);
        });

        // Payment methods management || working get the rest not yet tested
        Route::prefix('admin/payment-methods')->group(function () {
            Route::post('create', [PaymentMethodController::class, 'createPaymentMethod']);
            Route::get('list', [PaymentMethodController::class, 'getPaymentMethods']);
            Route::put('{paymentMethod}/update', [PaymentMethodController::class, 'updatePaymentMethod']);
            Route::delete('{paymentMethod}/delete', [PaymentMethodController::class, 'deletePaymentMethod']);
        });

        // Analytics and reports || working perfectly
        Route::prefix('admin/reports')->group(function () {
            Route::get('revenue', [DashboardController::class, 'revenueReport']);
            Route::get('occupancy', [DashboardController::class, 'occupancyReport']);
            Route::get('maintenance', [DashboardController::class, 'maintenanceReport']);
            Route::get('users', [DashboardController::class, 'usersReport']);
        });
    });

    // Landlord-only routes  || all good here
    Route::middleware('role:landlord')->group(function () {
        Route::prefix('landlord')->group(function () {
            Route::get('properties', [PropertyController::class, 'index']);
            Route::get('properties/{property}', [PropertyController::class, 'index']);
            Route::post('properties', [PropertyController::class, 'store']);
            Route::put('properties/{property}', [PropertyController::class, 'update']);
            Route::get('tenants', [TenantController::class, 'index']);
            Route::get('payments', [PaymentController::class, 'index']);
            Route::get('maintenance-requests', [MaintenanceRequestController::class, 'index']);
        });
    });

    // Tenant-only routes || get profile,
    Route::middleware('role:tenant')->group(function () {
        Route::prefix('tenant')->group(function () {
            Route::get('profile', [TenantController::class, 'show']);
            Route::get('payments', [PaymentController::class, 'index']);
            Route::get('maintenance-requests', [MaintenanceRequestController::class, 'index']);
            Route::post('maintenance-requests', [MaintenanceRequestController::class, 'store']);
        });
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found'
    ], 404);
});
