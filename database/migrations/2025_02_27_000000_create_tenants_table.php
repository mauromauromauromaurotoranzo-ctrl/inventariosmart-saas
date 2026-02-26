<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name'); // Nombre del negocio
            $table->string('slug')->unique(); // Subdominio: farmacia-san-juan
            $table->string('rubro'); // farmacia, retail, restaurante, etc
            $table->string('database')->unique(); // tenant_farmacia_001
            $table->string('plan')->default('starter'); // starter, professional, business
            $table->string('email')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->string('status')->default('pending'); // pending, active, suspended, cancelled
            $table->json('settings')->nullable(); // Config específica por rubro
            $table->timestamps();
            
            $table->index('status');
            $table->index('rubro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
