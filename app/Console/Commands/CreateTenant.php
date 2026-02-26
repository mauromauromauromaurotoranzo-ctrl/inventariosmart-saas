<?php

namespace App\Console\Commands;

use App\Application\UseCases\Tenant\RegisterTenantRequest;
use App\Application\UseCases\Tenant\RegisterTenantUseCase;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create 
                            {name : Nombre del negocio}
                            {rubro : Tipo de rubro (retail,farmacia,restaurante,ferreteria,moda,distribuidora,manufactura)}
                            {email : Email del administrador}
                            {--plan=starter : Plan (starter/professional/business)}
                            {--password= : Contraseña (opcional, se genera si no se especifica)}';
    
    protected $description = 'Crear un nuevo tenant con arquitectura multi-tenancy';

    public function __construct(
        private readonly RegisterTenantUseCase $registerTenantUseCase
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $rubro = $this->argument('rubro');
        $email = $this->argument('email');
        $plan = $this->option('plan');
        $password = $this->option('password') ?: $this->generatePassword();

        // Validar rubro
        $validRubros = ['retail', 'farmacia', 'restaurante', 'ferreteria', 'moda', 'distribuidora', 'manufactura'];
        if (!in_array($rubro, $validRubros)) {
            $this->error("Rubro no válido. Opciones: " . implode(', ', $validRubros));
            return self::FAILURE;
        }

        $this->info("Creando tenant...");
        $this->info("Nombre: {$name}");
        $this->info("Rubro: {$rubro}");
        $this->info("Plan: {$plan}");

        try {
            $request = new RegisterTenantRequest(
                name: $name,
                slug: $this->generateSlug($name),
                rubro: $rubro,
                email: $email,
                password: $password,
                plan: $plan
            );

            $response = $this->registerTenantUseCase->execute($request);

            if (!$response->success) {
                $this->error("Error: {$response->error}");
                return self::FAILURE;
            }

            $this->newLine();
            $this->info("✅ Tenant creado exitosamente!");
            $this->newLine();
            $this->info("📧 Email: {$email}");
            $this->info("🔑 Contraseña: {$password}");
            $this->info("🌐 URL: https://{$response->tenantSlug}.inventariosmart.app");
            $this->newLine();
            $this->info("💡 El tenant tiene 14 días de prueba gratuita.");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error al crear tenant: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        return substr($slug, 0, 50);
    }

    private function generatePassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
