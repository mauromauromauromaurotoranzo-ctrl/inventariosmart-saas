<?php

namespace App\Http\Middleware;

use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Domain\ValueObjects\TenantSlug;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Obtener subdominio
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        
        // Excluir subdominios reservados
        if (in_array($subdomain, ['www', 'app', 'admin', 'api', 'localhost', '127'])) {
            return $next($request);
        }
        
        // Buscar tenant por slug
        try {
            $slug = TenantSlug::fromString($subdomain);
            $tenant = $this->tenantRepository->findBySlug($slug);
            
            if (!$tenant) {
                abort(404, 'Tenant no encontrado');
            }
            
            // Verificar trial/suscripción
            if ($tenant->hasExpiredTrial() && !$tenant->subscribedAt()) {
                abort(403, 'Suscripción requerida. Tu período de prueba ha expirado.');
            }
            
            // Verificar estado
            if (!$tenant->isActive()) {
                abort(403, 'Tu cuenta está suspendida. Contacta a soporte.');
            }
            
            // Guardar en request para uso posterior
            $request->attributes->set('tenant', $tenant);
            app()->instance('tenant', $tenant);
            
            // Configurar conexión a BD del tenant si es necesario
            config(['database.connections.tenant.database' => $tenant->database()]);
            \DB::purge('tenant');
            \DB::reconnect('tenant');
            
        } catch (\Exception $e) {
            if ($e->getCode() === 404 || $e->getCode() === 403) {
                throw $e;
            }
            // Si hay error de parsing del slug, continuar sin tenant
        }
        
        return $next($request);
    }
}
