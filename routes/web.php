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
use App\Http\Controllers\TrafficController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\MailSettingController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\HotspotController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PaymentSettingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SiteSettingController;


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Bisa diakses siapa saja)
|--------------------------------------------------------------------------
*/

// Halaman Depan (Cek Tagihan)
Route::get('/', [FrontendController::class, 'index'])->name('frontend.index');
Route::get('/paket', [FrontendController::class, 'pricing'])->name('frontend.pricing');
Route::get('/tentang-kami', [FrontendController::class, 'about'])->name('frontend.about');
Route::get('/syarat-ketentuan', [FrontendController::class, 'terms'])->name('frontend.terms');
Route::post('/check-bill', [FrontendController::class, 'check'])->name('frontend.check');
Route::get('/invoice/{id}/download', [FrontendController::class, 'downloadInvoice'])->name('frontend.invoice');

// Login, Register & Reset Password
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Registration
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Account Activation (Public)
    Route::get('/activate/{token}', [AuthController::class, 'activateUser'])->name('activate.user');
});

// Activation & Notifications (Requires Auth)
Route::middleware(['auth'])->group(function () {
    Route::post('/request-activation', [AuthController::class, 'requestRouterActivation'])->name('request.activation');

    Route::get('/notifications', [AuthController::class, 'getNotifications'])->name('notifications.index');
    Route::post('/notifications/mark-read', [AuthController::class, 'markNotificationsRead'])->name('notifications.markRead');
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');


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

    //Dashboard bisa di akses Admin dan Operator
    Route::get('/admin/dashboard', [PppoeController::class, 'index'])->name('pppoe.dashboard');
    //Route Maps Pelanggan
    Route::get('/maps', [App\Http\Controllers\MapController::class, 'index'])->name('maps.index');

    // ... (SISA SEMUA ROUTE LAMA ANDA : BILLING, ADMIN, OPERATOR TETAP DISINI) ...

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/{id}/pay', [BillingController::class, 'processPayment'])->name('billing.pay');
    Route::post('/billing/{id}/pay-ajax', [BillingController::class, 'processPaymentAjax'])->name('billing.payAjax');

    Route::post('/billing/{id}/cancel', [BillingController::class, 'cancelPayment'])->name('billing.cancel');
    Route::post('/billing/store', [BillingController::class, 'store'])->name('billing.store');
    Route::post('/billing/generate', [BillingController::class, 'generate'])->name('billing.generate');
    // AJAX Bulk Billing
    Route::get('/billing/generate-list', [BillingController::class, 'getList'])->name('billing.list');
    Route::post('/billing/generate-process', [BillingController::class, 'processItem'])->name('billing.process');
    Route::get('/billing/{id}/print', [BillingController::class, 'print'])->name('billing.print');
    Route::delete('/billing/bulk-destroy', [BillingController::class, 'bulkDestroy'])->name('billing.bulkDestroy');
    Route::delete('/billing/{id}', [BillingController::class, 'destroy'])->name('billing.destroy');

    // Report
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');

    // Admin Only
    Route::middleware(['role:admin,superadmin'])->group(function () {
        // EXPORT & IMPORT
        Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
        Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');
        Route::get('/customers/template', [CustomerController::class, 'downloadTemplate'])->name('customers.template');
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
        Route::get('/whatsapp/broadcast', [WhatsappController::class, 'broadcastIndex'])->name('whatsapp.broadcast.index');
        Route::post('/whatsapp/broadcast/process', [WhatsappController::class, 'broadcastProcess'])->name('whatsapp.broadcast.process');
        // API Helper untuk Broadcast
        Route::get('/whatsapp/broadcast/targets', [WhatsappController::class, 'getBroadcastTargets'])->name('whatsapp.broadcast.targets');
        // Route Helper Gateway (Essential only)
        Route::post('/whatsapp/api-key', [WhatsappController::class, 'regenerateApiKey'])->name('whatsapp.apikey');

        // Route Proses Kirim (yang sudah dibuat sebelumnya)
        Route::post('/whatsapp/broadcast/process', [WhatsappController::class, 'broadcastProcess'])->name('whatsapp.broadcast.process');
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // AKUNTANSI & KEUANGAN
        Route::get('/accounting', [AccountingController::class, 'index'])->name('accounting.index');
        Route::post('/accounting/expense', [AccountingController::class, 'storeExpense'])->name('accounting.store');
        Route::delete('/accounting/expense/{id}', [AccountingController::class, 'destroyExpense'])->name('accounting.destroy');
        Route::get('/accounting/print', [AccountingController::class, 'print'])->name('accounting.print');

        // TRAFFIC MONITOR
        Route::get('/traffic', [TrafficController::class, 'index'])->name('traffic.index');
        Route::post('/traffic/data', [TrafficController::class, 'data'])->name('traffic.data');

        // ROUTER SETTINGS (CRUD & SWITCH)
        Route::get('/router-setting', [RouterSettingController::class, 'index'])->name('router.index');
        Route::post('/router-setting', [RouterSettingController::class, 'store'])->name('router.store'); // Create & Update
        Route::post('/router-setting/activate/{id}', [RouterSettingController::class, 'activate'])->name('router.activate');
        Route::delete('/router-setting/{id}', [RouterSettingController::class, 'destroy'])->name('router.destroy');

        Route::get('/paket-plan', [PlanController::class, 'publicIndex'])->name('plans.public');
        Route::post('/paket-plan/checkout', [SubscriptionController::class, 'checkout'])->name('plans.checkout');



        // SUPERADMIN ONLY (System & Mail)
        Route::middleware(['role:superadmin'])->group(function () {
            // SYSTEM UPDATE
            Route::get('/system/update', [SystemController::class, 'index'])->name('system.index');
            Route::post('/system/update', [SystemController::class, 'update'])->name('system.update');
            Route::post('/system/clear-cache', [SystemController::class, 'clearCache'])->name('system.clear-cache');

            // MAIL SETTINGS
            Route::get('/mail/setting', [MailSettingController::class, 'index'])->name('mail.index');
            Route::post('/mail/setting', [MailSettingController::class, 'update'])->name('mail.update');
            Route::post('/mail/test', [MailSettingController::class, 'sendTestEmail'])->name('mail.test');

            // PLAN MANAGEMENT
            Route::resource('plans', PlanController::class);

            // PAYMENT SETTINGS
            Route::get('/settings/payment', [PaymentSettingController::class, 'index'])->name('payment.index');
            Route::post('/settings/payment', [PaymentSettingController::class, 'update'])->name('payment.update');

            Route::get('/settings/site', [SiteSettingController::class, 'index'])->name('site.index');
            Route::post('/settings/site', [SiteSettingController::class, 'update'])->name('site.update');
            Route::post('/users/{id}/suspend', [UserController::class, 'suspendSubscription'])->name('users.suspend');
            Route::post('/users/{id}/remove-plan', [UserController::class, 'removeSubscription'])->name('users.removePlan');
        });

        Route::resource('users', UserController::class);
    });

    // Hotspot Routes (Admin & Operator)
    Route::prefix('hotspot')->name('hotspot.')->group(function () {
        Route::get('/monitor', [HotspotController::class, 'monitor'])->name('monitor');
        Route::get('/generate', [HotspotController::class, 'generateForm'])->name('generate');
        Route::post('/generate', [HotspotController::class, 'generateStore'])->name('generate.store');
        Route::delete('/user/{name}', [HotspotController::class, 'destroy'])->name('destroy');
    });

    // Monitor Routes (Admin & Operator)
    Route::prefix('monitor')->name('monitor.')->group(function () {
        Route::get('/dhcp-leases', [MonitorController::class, 'dhcpLeases'])->name('dhcp-leases');
        Route::get('/static-users', [MonitorController::class, 'staticUsers'])->name('static-users');
        Route::get('/simple-queues', [MonitorController::class, 'simpleQueues'])->name('simple-queues');
        Route::get('/simple-queues-json', [MonitorController::class, 'getSimpleQueuesJson'])->name('simple-queues-json');
    });

    // CUSTOMER MANAGEMENT (Accessible by Admin & Operator)
    Route::post('/customers/destroy-all', [CustomerController::class, 'destroyAll'])->name('customers.destroyAll');
    Route::resource('customers', CustomerController::class);
});

Route::match(['get', 'post'], '/payment/webhook', [SubscriptionController::class, 'webhook'])->name('payment.webhook');
Route::get('/payment/finish', [SubscriptionController::class, 'paymentFinish'])->name('payment.finish');
Route::get('/payment/unfinish', [SubscriptionController::class, 'paymentUnfinish'])->name('payment.unfinish');
Route::get('/payment/error', [SubscriptionController::class, 'paymentError'])->name('payment.error');
