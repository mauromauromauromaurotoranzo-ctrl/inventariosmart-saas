<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class TenantModel extends Model
{
    /**
     * Conexión a usar (se sobrescribe en constructor)
     */
    protected $connection = 'tenant';
    
    /**
     * Indica si el modelo usa tenant_id
     */
    protected bool $usesTenantId = false;

    /**
     * Constructor
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Asegurar que usemos la conexión tenant
        $this->connection = 'tenant';
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();
        
        // Al crear, agregar tenant_id si corresponde
        static::creating(function ($model) {
            if ($model->usesTenantId && app()->has('tenant')) {
                $tenant = app('tenant');
                $model->tenant_id = $tenant->id;
                
                Log::debug("Asignando tenant_id {$tenant->id} a " . get_class($model));
            }
        });
        
        // Scope global para filtrar por tenant si es necesario
        static::addGlobalScope('tenant', function ($builder) use (&$model) {
            // Solo aplicar si el modelo usa tenant_id y hay un tenant activo
            $instance = new static;
            if ($instance->usesTenantId && app()->has('tenant')) {
                $tenant = app('tenant');
                $builder->where('tenant_id', $tenant->id);
            }
        });
    }

    /**
     * Obtener el tenant actual
     */
    public function getCurrentTenant(): ?Tenant
    {
        return app('tenant') ?? null;
    }

    /**
     * Verificar si hay un tenant conectado
     */
    public function hasTenant(): bool
    {
        return app()->has('tenant') && app('tenant') !== null;
    }
}
