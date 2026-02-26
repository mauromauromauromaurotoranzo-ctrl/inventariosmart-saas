<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'rubro',
        'database',
        'plan',
        'trial_ends_at',
        'subscribed_at',
        'status',
        'settings',
        'email',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscribed_at' => 'datetime',
    ];

    /**
     * Conectar a la base de datos del tenant
     */
    public function connect(): self
    {
        try {
            config(['database.connections.tenant.database' => $this->database]);
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            Log::info("Conectado a tenant: {$this->slug} ({$this->database})");
            
            return $this;
        } catch (\Exception $e) {
            Log::error("Error conectando a tenant {$this->slug}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crear base de datos del tenant
     */
    public function createDatabase(): self
    {
        try {
            // Crear BD si no existe
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$this->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            Log::info("Base de datos creada: {$this->database}");
            
            return $this;
        } catch (\Exception $e) {
            Log::error("Error creando BD {$this->database}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar base de datos del tenant
     */
    public function deleteDatabase(): self
    {
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$this->database}`");
            
            Log::info("Base de datos eliminada: {$this->database}");
            
            return $this;
        } catch (\Exception $e) {
            Log::error("Error eliminando BD {$this->database}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar si está en período de prueba
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Verificar si el trial ha expirado
     */
    public function hasExpiredTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Verificar si está activo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar si está suspendido
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Activar tenant
     */
    public function activate(): self
    {
        $this->update(['status' => 'active']);
        return $this;
    }

    /**
     * Suspender tenant
     */
    public function suspend(): self
    {
        $this->update(['status' => 'suspended']);
        return $this;
    }

    /**
     * Cancelar tenant
     */
    public function cancel(): self
    {
        $this->update(['status' => 'cancelled']);
        return $this;
    }

    /**
     * Marcar como suscrito
     */
    public function markAsSubscribed(): self
    {
        $this->update([
            'subscribed_at' => now(),
            'trial_ends_at' => null,
        ]);
        return $this;
    }

    /**
     * Obtener URL del tenant
     */
    public function getUrl(): string
    {
        $domain = config('app.domain', 'inventariosmart.app');
        return "https://{$this->slug}.{$domain}";
    }

    /**
     * Scope para tenants activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope por rubro
     */
    public function scopeByRubro($query, string $rubro)
    {
        return $query->where('rubro', $rubro);
    }

    /**
     * Scope por plan
     */
    public function scopeByPlan($query, string $plan)
    {
        return $query->where('plan', $plan);
    }

    /**
     * Scope para trials activos
     */
    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
                     ->where('trial_ends_at', '>', now());
    }

    /**
     * Scope para trials expirados
     */
    public function scopeExpiredTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
                     ->where('trial_ends_at', '<=', now())
                     ->whereNull('subscribed_at');
    }

    /**
     * Obtener settings por defecto según rubro
     */
    public static function getDefaultSettings(string $rubro): array
    {
        $settings = [
            'retail' => [
                'features' => ['escaner', 'promociones', 'multi_sucursal', 'etiquetas'],
                'onboarding_steps' => ['productos', 'caja', 'ventas'],
                'max_productos' => 1000,
                'max_sucursales' => 3,
                'max_usuarios' => 5,
            ],
            'farmacia' => [
                'features' => ['lotes', 'vencimientos', 'obras_sociales', 'trazabilidad'],
                'onboarding_steps' => ['medicamentos', 'proveedores', 'obras_sociales'],
                'max_productos' => 5000,
                'max_sucursales' => 5,
                'max_usuarios' => 10,
            ],
            'restaurante' => [
                'features' => ['recetas', 'mermas', 'insumos', 'areas_cocina'],
                'onboarding_steps' => ['platos', 'insumos', 'proveedores'],
                'max_productos' => 2000,
                'max_sucursales' => 2,
                'max_usuarios' => 8,
            ],
            'ferreteria' => [
                'features' => ['categorias_profundas', 'equivalentes', 'listas_precios'],
                'onboarding_steps' => ['categorias', 'productos', 'clientes'],
                'max_productos' => 10000,
                'max_sucursales' => 3,
                'max_usuarios' => 6,
            ],
            'moda' => [
                'features' => ['tallas_colores', 'temporadas', 'liquidacion'],
                'onboarding_steps' => ['productos', 'variantes', 'temporada'],
                'max_productos' => 3000,
                'max_sucursales' => 5,
                'max_usuarios' => 8,
            ],
            'distribuidora' => [
                'features' => ['listas_precios', 'rutas', 'backorder', 'portal_clientes'],
                'onboarding_steps' => ['productos', 'clientes', 'rutas'],
                'max_productos' => 15000,
                'max_sucursales' => 10,
                'max_usuarios' => 20,
            ],
            'manufactura' => [
                'features' => ['bom', 'ordenes_produccion', 'materia_prima', 'calidad'],
                'onboarding_steps' => ['materia_prima', 'productos_terminados', 'recetas'],
                'max_productos' => 5000,
                'max_sucursales' => 3,
                'max_usuarios' => 15,
            ],
        ];

        return $settings[$rubro] ?? $settings['retail'];
    }
}
