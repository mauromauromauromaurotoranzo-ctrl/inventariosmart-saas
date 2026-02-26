<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Subdominios reservados que no son tenants
     */
    protected array $reservedSubdomains = [
        'www',
        'app', 
        'admin',
        'api',
        'dashboard',
        'panel',
        'secure',
        'mail',
        'ftp',
        'smtp',
        'pop',
        'imap',
        'cpanel',
        'webmail',
        'webdisk',
        'ns1',
        'ns2',
        'localhost',
        'test',
        'demo',
        'staging',
        'dev',
        'beta',
        'alpha',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener host y extraer subdominio
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Si es IP o localhost, continuar sin tenant
        if ($this->isLocalhostOrIp($host)) {
            return $next($request);
        }
        
        // Extraer subdominio (primer parte del host)
        $subdomain = $parts[0];
        
        // Verificar si es un subdominio reservado
        if ($this->isReservedSubdomain($subdomain)) {
            return $next($request);
        }
        
        // Buscar tenant por slug
        $tenant = $this->findTenant($subdomain);
        
        if (!$tenant) {
            Log::warning("Tenant no encontrado: {$subdomain}");
            return $this->tenantNotFoundResponse();
        }
        
        // Verificar estado del tenant
        if (!$this->isTenantAccessible($tenant)) {
            return $this->tenantNotAccessibleResponse($tenant);
        }
        
        // Verificar trial/suscripción
        if (!$this->hasValidSubscription($tenant)) {
            return $this->subscriptionRequiredResponse($tenant);
        }
        
        // Conectar a la base de datos del tenant
        try {
            $tenant->connect();
        } catch (\Exception $e) {
            Log::error("Error conectando a BD del tenant {$tenant->slug}: " . $e->getMessage());
            return $this->databaseConnectionErrorResponse();
        }
        
        // Guardar tenant en el contenedor de la aplicación
        app()->instance('tenant', $tenant);
        
        // Agregar a la request para uso en controladores
        $request->merge(['tenant' => $tenant]);
        $request->setUserResolver(function () use ($tenant) {
            return $tenant;
        });
        
        // Compartir con vistas
        view()->share('tenant', $tenant);
        
        Log::info("Tenant identificado: {$tenant->slug} ({$tenant->name})");
        
        return $next($request);
    }

    /**
     * Verificar si es localhost o IP
     */
    protected function isLocalhostOrIp(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1']) || 
               filter_var($host, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Verificar si es un subdominio reservado
     */
    protected function isReservedSubdomain(string $subdomain): bool
    {
        return in_array(strtolower($subdomain), $this->reservedSubdomains);
    }

    /**
     * Buscar tenant por slug
     */
    protected function findTenant(string $slug): ?Tenant
    {
        return Tenant::where('slug', $slug)->first();
    }

    /**
     * Verificar si el tenant está accesible
     */
    protected function isTenantAccessible(Tenant $tenant): bool
    {
        return $tenant->isActive() || $tenant->isOnTrial();
    }

    /**
     * Verificar si tiene suscripción válida
     */
    protected function hasValidSubscription(Tenant $tenant): bool
    {
        // Si está en trial, es válido
        if ($tenant->isOnTrial()) {
            return true;
        }
        
        // Si tiene fecha de suscripción, es válido
        if ($tenant->subscribed_at) {
            return true;
        }
        
        // Trial expirado y no suscrito
        return false;
    }

    /**
     * Respuesta cuando el tenant no existe
     */
    protected function tenantNotFoundResponse(): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'Tenant no encontrado',
                'message' => 'El subdominio no existe en nuestro sistema'
            ], 404);
        }
        
        return response()->view('errors.tenant-not-found', [], 404);
    }

    /**
     * Respuesta cuando el tenant no está accesible
     */
    protected function tenantNotAccessibleResponse(Tenant $tenant): Response
    {
        $message = match($tenant->status) {
            'suspended' => 'Tu cuenta ha sido suspendida. Contacta a soporte.',
            'cancelled' => 'Tu cuenta ha sido cancelada.',
            default => 'Cuenta no disponible.',
        };
        
        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'Cuenta no accesible',
                'message' => $message,
                'status' => $tenant->status
            ], 403);
        }
        
        return response()->view('errors.tenant-suspended', [
            'message' => $message,
            'tenant' => $tenant
        ], 403);
    }

    /**
     * Respuesta cuando se requiere suscripción
     */
    protected function subscriptionRequiredResponse(Tenant $tenant): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'Suscripción requerida',
                'message' => 'Tu período de prueba ha expirado. Suscríbete para continuar.',
                'trial_ended' => true,
                'subscribe_url' => $tenant->getUrl() . '/subscribe'
            ], 402); // Payment Required
        }
        
        return redirect()->away($tenant->getUrl() . '/subscribe');
    }

    /**
     * Respuesta cuando hay error de conexión a BD
     */
    protected function databaseConnectionErrorResponse(): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'Error de conexión',
                'message' => 'No se pudo conectar a la base de datos. Intenta más tarde.'
            ], 500);
        }
        
        return response()->view('errors.database-connection', [], 500);
    }
}
