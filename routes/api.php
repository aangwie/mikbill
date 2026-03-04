<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsappController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDashboardController;
use App\Http\Controllers\Api\MobileCustomerController;
use App\Http\Controllers\Api\MobileInvoiceController;
use App\Http\Controllers\Api\MobileCompanyController;
use App\Http\Controllers\Api\MobileSettingController;
use App\Http\Controllers\Api\MobileMapController;

// Existing WhatsApp API
Route::post('/send-message', [WhatsappController::class, 'sendMessage']);

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
| Prefix: /api/mobile
| Auth: Laravel Sanctum (token-based)
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')->group(function () {

    // ── Public Routes ──
    Route::post('/login', [MobileAuthController::class, 'login']);
    Route::get('/check-bill', [MobileInvoiceController::class, 'checkBill']);
    Route::get('/app-config', [MobileSettingController::class, 'appConfig']);

    // ── Protected Routes (Require Sanctum Token) ──
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [MobileAuthController::class, 'logout']);
        Route::get('/profile', [MobileAuthController::class, 'profile']);

        // Dashboard
        Route::get('/dashboard', [MobileDashboardController::class, 'index']);

        // Customers CRUD
        Route::get('/customers', [MobileCustomerController::class, 'index']);
        Route::get('/customers/{id}', [MobileCustomerController::class, 'show']);
        Route::post('/customers', [MobileCustomerController::class, 'store']);
        Route::put('/customers/{id}', [MobileCustomerController::class, 'update']);
        Route::delete('/customers/{id}', [MobileCustomerController::class, 'destroy']);

        // Invoices
        Route::get('/invoices', [MobileInvoiceController::class, 'index']);
        Route::post('/invoices/{id}/pay', [MobileInvoiceController::class, 'pay']);
        Route::post('/invoices/{id}/cancel', [MobileInvoiceController::class, 'cancel']);

        // Company Info
        Route::get('/company', [MobileCompanyController::class, 'index']);

        Route::get('/maps/online-users', [MobileMapController::class, 'index']);
    });
});
