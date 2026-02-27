<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\TenantRegisterController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas públicas (sin tenant)
|
*/

// Registro de nuevos tenants (auto-provisioning)
Route::post('/register', [TenantRegisterController::class, 'store']);
Route::get('/check-slug', [TenantRegisterController::class, 'checkSlugAvailability']);

// Planes de suscripción
Route::get('/plans', [SubscriptionController::class, 'plans']);

/*
|--------------------------------------------------------------------------
| Rutas de Tenant (requieren subdominio)
|--------------------------------------------------------------------------
*/

Route::middleware(['tenant'])->group(function () {
    
    // Auth
    Route::post('/login', [TenantLoginController::class, 'login']);
    Route::post('/forgot-password', [TenantLoginController::class, 'forgotPassword']);
    
    // Suscripciones
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout']);
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])
        ->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');
    
    // Onboarding (público durante trial)
    Route::get('/onboarding/status', [OnboardingController::class, 'status']);
    Route::get('/onboarding/step/{step}', [OnboardingController::class, 'getStepConfig']);
    Route::post('/onboarding/step/{step}', [OnboardingController::class, 'saveStep']);
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete']);
    
    // Rutas protegidas (requieren auth)
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Auth
        Route::post('/logout', [TenantLoginController::class, 'logout']);
        Route::get('/me', [TenantLoginController::class, 'me']);
        Route::post('/change-password', [TenantLoginController::class, 'changePassword']);
        
        // Onboarding (guardar progreso)
        Route::post('/onboarding/step/{step}', [OnboardingController::class, 'saveStep']);
        
        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/ventas-chart', [DashboardController::class, 'ventasChart']);
        Route::get('/dashboard/top-productos', [DashboardController::class, 'topProductos']);
        Route::get('/dashboard/alertas', [DashboardController::class, 'alertas']);
        
        // Productos
        Route::apiResource('productos', ProductoController::class);
        Route::get('/productos/stats/resumen', [ProductoController::class, 'stats']);
        
        // Categorías
        Route::apiResource('categorias', CategoriaController::class);
        
        // Clientes
        Route::apiResource('clientes', ClienteController::class);
        Route::get('/clientes/stats/resumen', [ClienteController::class, 'stats']);
        
        // Ventas
        Route::apiResource('ventas', VentaController::class);
        Route::post('/ventas/{id}/cancelar', [VentaController::class, 'cancelar']);
        Route::get('/ventas/stats/resumen', [VentaController::class, 'stats']);
        Route::get('/ventas/stats/top-productos', [VentaController::class, 'topProductos']);
        
        // Configuración
        Route::get('/settings', function () {
            $tenant = app('tenant');
            return response()->json([
                'name' => $tenant->name,
                'rubro' => $tenant->rubro,
                'plan' => $tenant->plan,
                'settings' => $tenant->settings,
            ]);
        });
        
        // Funciones específicas por rubro
        Route::middleware(['rubro:farmacia'])->group(function () {
            Route::apiResource('lotes', \App\Http\Controllers\LoteController::class);
            Route::apiResource('obras-sociales', \App\Http\Controllers\ObraSocialController::class);
        });
        
        Route::middleware(['rubro:restaurante'])->group(function () {
            Route::apiResource('recetas', \App\Http\Controllers\RecetaController::class);
            Route::apiResource('areas', \App\Http\Controllers\AreaController::class);
        });
        
        Route::middleware(['rubro:distribuidora'])->group(function () {
            Route::apiResource('rutas', \App\Http\Controllers\RutaController::class);
            Route::get('/clientes/{cliente}/precios', [
                \App\Http\Controllers\ClienteController::class, 'listaPrecios'
            ]);
        });
        
    });
    
});

/*
|--------------------------------------------------------------------------
| Webhooks
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/stripe', [SubscriptionController::class, 'webhook']);
