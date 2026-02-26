<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del negocio
            $table->string('slug')->unique(); // Subdominio: farmacia-san-juan
            $table->enum('rubro', [
                'retail', 
                'farmacia', 
                'restaurante', 
                'ferreteria', 
                'moda', 
                'distribuidora', 
                'manufactura'
            ]);
            $table->string('database')->unique(); // tenant_farmacia_001
            $table->enum('plan', ['starter', 'professional', 'business', 'enterprise'])
                  ->default('starter');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->enum('status', ['active', 'suspended', 'cancelled'])
                  ->default('active');
            $table->json('settings')->nullable(); // Config específica por rubro
            $table->string('email')->unique(); // Email del administrador
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('rubro');
            $table->index('status');
            $table->index(['status', 'trial_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
