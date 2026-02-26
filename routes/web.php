<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;

// Landing page (pública)
Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/precios', [LandingController::class, 'pricing'])->name('landing.pricing');
Route::get('/registro', [LandingController::class, 'showRegistrationForm'])->name('landing.register');
Route::post('/registro', [LandingController::class, 'register'])->name('landing.register.post');

// Onboarding Wizard
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'showWizard'])->name('onboarding.wizard');
});

// API Routes for Onboarding
Route::middleware(['auth', 'tenant'])->prefix('api/onboarding')->group(function () {
    Route::post('/start', [OnboardingController::class, 'start'])->name('api.onboarding.start');
    Route::get('/status', [OnboardingController::class, 'status'])->name('api.onboarding.status');
    Route::post('/complete-step', [OnboardingController::class, 'completeStep'])->name('api.onboarding.complete-step');
});

// Payment routes
Route::post('/payment/checkout', [PaymentController::class, 'createCheckoutSession'])->name('payment.checkout');
Route::get('/payment/success/{tenant}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel/{tenant}', [PaymentController::class, 'cancel'])->name('payment.cancel');

// Stripe Webhook (public - no CSRF)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Rutas de autenticación tenant
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Placeholder routes - se implementarán después
    Route::get('/categorias', function() { return view('pages.categorias'); })->name('categorias.index');
    Route::get('/productos', function() { return view('pages.productos'); })->name('productos.index');
    Route::get('/aumento-masivo-precios', function() { return view('pages.aumento-masivo'); })->name('aumento-masivo.index');
    Route::get('/proveedores', function() { return view('pages.proveedores'); })->name('proveedores.index');
    Route::get('/clientes', function() { return view('pages.clientes'); })->name('clientes.index');
    Route::get('/cajas', function() { return view('pages.cajas'); })->name('cajas.index');
    Route::get('/cuentas-corrientes', function() { return view('pages.cuentas-corrientes'); })->name('cuentas-corrientes.index');
    Route::get('/deudas-clientes', function() { return view('pages.deudas-clientes'); })->name('deudas-clientes.index');
    Route::get('/movimientos-stock', function() { return view('pages.movimientos-stock'); })->name('movimientos-stock.index');
    Route::get('/ventas', function() { return view('pages.ventas'); })->name('ventas.index');
    Route::get('/ventas/{id}', function($id) { return view('pages.venta-detalle', ['id' => $id]); })->name('ventas.show');
    Route::get('/cheques', function() { return view('pages.cheques'); })->name('cheques.index');
});
