<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class TenantModel extends Model
{
    /**
     * Forzar uso de conexión tenant
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = 'tenant';
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Agregar tenant_id si es necesario y está disponible
            if (app()->has('tenant') && !isset($model->tenant_id)) {
                $tenant = app('tenant');
                if ($tenant) {
                    $model->tenant_id = $tenant->id()->value();
                }
            }
        });
    }

    /**
     * Scope para filtrar por tenant actual
     */
    public function scopeCurrentTenant($query)
    {
        if (app()->has('tenant')) {
            $tenant = app('tenant');
            if ($tenant) {
                return $query->where('tenant_id', $tenant->id()->value());
            }
        }
        return $query;
    }
}
