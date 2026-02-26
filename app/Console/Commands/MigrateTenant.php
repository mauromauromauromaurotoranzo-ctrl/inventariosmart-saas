<?php

namespace App\Console\Commands;

use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Domain\ValueObjects\TenantSlug;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateTenant extends Command
{
    protected $signature = 'tenant:migrate 
                            {slug? : Slug del tenant (opcional, si no se especifica migra todos)}
                            {--fresh : Eliminar tablas y recrear}
                            {--seed : Ejecutar seeders después de migrar}';
    
    protected $description = 'Ejecutar migraciones en la base de datos de un tenant o todos los tenants';

    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $slug = $this->argument('slug');

        if ($slug) {
            // Migrar un tenant específico
            return $this->migrateSingleTenant($slug);
        } else {
            // Migrar todos los tenants
            return $this->migrateAllTenants();
        }
    }

    private function migrateSingleTenant(string $slug): int
    {
        $this->info("Buscando tenant: {$slug}");

        try {
            $tenantSlug = TenantSlug::fromString($slug);
            $tenant = $this->tenantRepository->findBySlug($tenantSlug);

            if (!$tenant) {
                $this->error("Tenant no encontrado: {$slug}");
                return self::FAILURE;
            }

            $this->info("Migrando: {$tenant->name()}");
            $this->info("Base de datos: {$tenant->database()}");

            // Configurar conexión
            config(['database.connections.tenant.database' => $tenant->database()]);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Ejecutar migraciones
            $options = ['--database' => 'tenant', '--force' => true];
            
            if ($this->option('fresh')) {
                $this->call('migrate:fresh', $options);
                $this->info("✅ Base de datos recreada");
            } else {
                $this->call('migrate', $options);
                $this->info("✅ Migraciones ejecutadas");
            }

            if ($this->option('seed')) {
                $this->call('db:seed', $options);
                $this->info("✅ Seeders ejecutados");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function migrateAllTenants(): int
    {
        $this->info("Obteniendo todos los tenants...");

        $tenants = $this->tenantRepository->findAllActive();

        if (empty($tenants)) {
            $this->warn("No hay tenants activos");
            return self::SUCCESS;
        }

        $this->info("Migrando " . count($tenants) . " tenant(s)...");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        foreach ($tenants as $tenant) {
            try {
                $this->info("[{$tenant->slug()->value()}] {$tenant->name()}");

                // Configurar conexión
                config(['database.connections.tenant.database' => $tenant->database()]);
                DB::purge('tenant');
                DB::reconnect('tenant');

                // Ejecutar migraciones
                $options = ['--database' => 'tenant', '--force' => true];
                
                if ($this->option('fresh')) {
                    $this->call('migrate:fresh', $options);
                } else {
                    $this->call('migrate', $options);
                }

                if ($this->option('seed')) {
                    $this->call('db:seed', $options);
                }

                $this->info("  ✅ OK");
                $successCount++;

            } catch (\Exception $e) {
                $this->error("  ❌ Error: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("Resumen: {$successCount} exitosos, {$errorCount} errores");

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
