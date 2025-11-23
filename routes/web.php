<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PppoeController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\RouterSettingController; 

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Bisa diakses siapa saja)
|--------------------------------------------------------------------------
*/

// Halaman Depan (Cek Tagihan)
Route::get('/', [FrontendController::class, 'index'])->name('frontend.index');
Route::post('/check-bill', [FrontendController::class, 'check'])->name('frontend.check');
Route::get('/invoice/{id}/download', [FrontendController::class, 'downloadInvoice'])->name('frontend.invoice');

// Login & Logout
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Harus Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Redirect /dashboard ke billing index (atau dashboard admin jika mau)
    Route::get('/dashboard', function () {
        return redirect()->route('billing.index');
    });

    // ... (SISA SEMUA ROUTE LAMA ANDA : BILLING, ADMIN, OPERATOR TETAP DISINI) ...

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/{id}/pay', [BillingController::class, 'processPayment'])->name('billing.pay');
    Route::post('/billing/{id}/cancel', [BillingController::class, 'cancelPayment'])->name('billing.cancel');
    Route::post('/billing/store', [BillingController::class, 'store'])->name('billing.store');
    Route::post('/billing/generate', [BillingController::class, 'generate'])->name('billing.generate');
    Route::get('/billing/{id}/print', [BillingController::class, 'print'])->name('billing.print');

    // Report
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');

    // Admin Only
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [PppoeController::class, 'index'])->name('pppoe.dashboard');
        // ... dst ...
        Route::resource('users', UserController::class);
        Route::resource('customers', CustomerController::class);
        // ... (masukkan route admin lainnya disini seperti sebelumnya)
        Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
        Route::post('/company', [CompanyController::class, 'update'])->name('company.update');
        Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
        // ...
        Route::get('/sync/get-list', [CustomerController::class, 'syncGetList'])->name('sync.list');
        Route::post('/sync/process', [CustomerController::class, 'syncProcessItem'])->name('sync.process');
        Route::post('/pppoe/kick', [PppoeController::class, 'kick'])->name('pppoe.kick');
        Route::post('/pppoe/toggle', [PppoeController::class, 'toggle'])->name('pppoe.toggle');
        // ...
        Route::post('/whatsapp/update', [WhatsappController::class, 'update'])->name('whatsapp.update');
        Route::post('/whatsapp/test', [WhatsappController::class, 'sendTest'])->name('whatsapp.test');
        Route::post('/whatsapp/broadcast', [WhatsappController::class, 'broadcast'])->name('whatsapp.broadcast');
        Route::post('/whatsapp/send-customer', [WhatsappController::class, 'sendToCustomer'])->name('whatsapp.send.customer');
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // AKUNTANSI & KEUANGAN
        Route::get('/accounting', [AccountingController::class, 'index'])->name('accounting.index');
        Route::post('/accounting/expense', [AccountingController::class, 'storeExpense'])->name('accounting.store');
        Route::delete('/accounting/expense/{id}', [AccountingController::class, 'destroyExpense'])->name('accounting.destroy');
        Route::get('/accounting/print', [AccountingController::class, 'print'])->name('accounting.print');

        // Konfigurasi Mikrotik
        Route::get('/router-setting', [RouterSettingController::class, 'index'])->name('router.index');
        Route::post('/router-setting', [RouterSettingController::class, 'update'])->name('router.update');
    });
});
