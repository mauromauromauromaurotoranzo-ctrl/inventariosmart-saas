<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tenant;

use App\Domain\Entities\Tenant;
use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Domain\ValueObjects\TenantSlug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class RegisterTenantUseCase
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository
    ) {}

    public function execute(RegisterTenantRequest $request): RegisterTenantResponse
    {
        try {
            // Validar slug único
            $slug = TenantSlug::fromString($request->slug());
            if ($this->tenantRepository->existsBySlug($slug)) {
                return RegisterTenantResponse::failure('El slug ya está en uso');
            }

            // Generar nombre de base de datos único
            $database = $this->generateDatabaseName($slug->value());
            if ($this->tenantRepository->existsByDatabase($database)) {
                return RegisterTenantResponse::failure('Error generando base de datos, intente nuevamente');
            }

            // Crear entidad Tenant
            $tenant = Tenant::create(
                slug: $slug,
                name: $request->name(),
                rubro: $request->rubro(),
                database: $database,
                plan: $request->plan(),
                email: $request->email()
            );

            // Crear base de datos física
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            Log::info("Base de datos creada para tenant: {$database}");

            // Crear tablas del tenant
            \Illuminate\Support\Facades\Artisan::call('tenant:create-tables', ['database' => $database]);
            Log::info("Tablas creadas para tenant: {$database}");

            // Guardar tenant en BD central
            $this->tenantRepository->save($tenant);

            // Activar tenant
            $tenant->activate();
            $this->tenantRepository->save($tenant);

            // Enviar email de bienvenida
            if ($tenant->email()) {
                \Illuminate\Support\Facades\Mail::to($tenant->email())->send(
                    new \App\Mail\TenantWelcomeMail(\App\Models\Tenant::find($tenant->id()->value()))
                );
            }

            return RegisterTenantResponse::success($tenant);

        } catch (\Exception $e) {
            Log::error("Error registrando tenant: " . $e->getMessage());
            return RegisterTenantResponse::failure('Error interno: ' . $e->getMessage());
        }
    }

    private function generateDatabaseName(string $slug): string
    {
        $baseName = 'tenant_' . $slug;
        $database = $baseName;
        $counter = 1;

        while ($this->tenantRepository->existsByDatabase($database)) {
            $database = $baseName . '_' . $counter;
            $counter++;
        }

        return $database;
    }
}
