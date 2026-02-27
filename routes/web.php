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

// Payment routes (protegidas)
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('/payment/checkout', [PaymentController::class, 'createCheckoutSession'])->name('payment.checkout');
    Route::get('/payment/history', [PaymentController::class, 'history'])->name('payment.history');
});

Route::get('/payment/success/{tenant}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel/{tenant}', [PaymentController::class, 'cancel'])->name('payment.cancel');

// Stripe Webhook (public - no CSRF)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Rutas de autenticación tenant
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas del panel administrativo
Route::middleware(['auth', 'tenant'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Productos
    Route::get('/productos', function() { 
        return view('pages.productos.index'); 
    })->name('productos.index');
    
    // Categorías
    Route::get('/categorias', function() { 
        return view('pages.categorias.index'); 
    })->name('categorias.index');
    
    // Clientes
    Route::get('/clientes', function() { 
        return view('pages.clientes.index'); 
    })->name('clientes.index');
    
    // Proveedores
    Route::get('/proveedores', function() { 
        return view('pages.proveedores.index'); 
    })->name('proveedores.index');
    
    // Ventas
    Route::get('/ventas', function() { 
        return view('pages.ventas.index'); 
    })->name('ventas.index');
    
    Route::get('/ventas/pos', function() { 
        return view('pages.ventas.pos'); 
    })->name('ventas.pos');
    
    Route::get('/ventas/{id}', function($id) { 
        return view('pages.ventas.show', ['id' => $id]); 
    })->name('ventas.show');
    
    // Cajas
    Route::get('/cajas', function() { 
        return view('pages.cajas.index'); 
    })->name('cajas.index');
    
    // Cuentas Corrientes
    Route::get('/cuentas-corrientes', function() { 
        return view('pages.cuentas-corrientes.index'); 
    })->name('cuentas-corrientes.index');
    
    // Deudas de Clientes
    Route::get('/deudas-clientes', function() { 
        return view('pages.deudas-clientes.index'); 
    })->name('deudas-clientes.index');
    
    // Movimientos de Stock
    Route::get('/movimientos-stock', function() { 
        return view('pages.movimientos-stock.index'); 
    })->name('movimientos-stock.index');
    
    // Aumento Masivo de Precios
    Route::get('/aumento-masivo-precios', function() { 
        return view('pages.aumento-masivo.index'); 
    })->name('aumento-masivo.index');
    
    // Cheques
    Route::get('/cheques', function() { 
        return view('pages.cheques.index'); 
    })->name('cheques.index');
});
