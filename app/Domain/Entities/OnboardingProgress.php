<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\TenantId;
use DateTimeImmutable;

final class OnboardingProgress
{
    private array $completedSteps = [];
    private ?DateTimeImmutable $startedAt;
    private ?DateTimeImmutable $completedAt = null;

    private function __construct(
        private readonly string $id,
        private readonly TenantId $tenantId,
        private string $currentStep,
        array $completedSteps = [],
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $completedAt = null,
        private array $stepData = []
    ) {
        $this->completedSteps = $completedSteps;
        $this->startedAt = $startedAt ?? now()->toDateTimeImmutable();
        $this->completedAt = $completedAt;
    }

    public static function start(TenantId $tenantId, string $rubro): self
    {
        $steps = self::getStepsForRubro($rubro);
        
        return new self(
            id: uniqid('onboarding_', true),
            tenantId: $tenantId,
            currentStep: $steps[0],
            startedAt: now()->toDateTimeImmutable()
        );
    }

    public static function reconstitute(
        string $id,
        TenantId $tenantId,
        string $currentStep,
        array $completedSteps,
        DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
        array $stepData
    ): self {
        return new self(
            id: $id,
            tenantId: $tenantId,
            currentStep: $currentStep,
            completedSteps: $completedSteps,
            startedAt: $startedAt,
            completedAt: $completedAt,
            stepData: $stepData
        );
    }

    // Getters
    public function id(): string { return $this->id; }
    public function tenantId(): TenantId { return $this->tenantId; }
    public function currentStep(): string { return $this->currentStep; }
    public function completedSteps(): array { return $this->completedSteps; }
    public function startedAt(): DateTimeImmutable { return $this->startedAt; }
    public function completedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function stepData(): array { return $this->stepData; }

    // Domain methods
    public function completeCurrentStep(array $data = []): void
    {
        if (!in_array($this->currentStep, $this->completedSteps)) {
            $this->completedSteps[] = $this->currentStep;
        }
        
        $this->stepData[$this->currentStep] = $data;
        
        // Avanzar al siguiente paso
        $allSteps = $this->getAllSteps();
        $currentIndex = array_search($this->currentStep, $allSteps);
        
        if ($currentIndex !== false && isset($allSteps[$currentIndex + 1])) {
            $this->currentStep = $allSteps[$currentIndex + 1];
        } else {
            $this->completeOnboarding();
        }
    }

    public function skipCurrentStep(): void
    {
        if (!in_array($this->currentStep, $this->completedSteps)) {
            $this->completedSteps[] = $this->currentStep;
        }
        
        $allSteps = $this->getAllSteps();
        $currentIndex = array_search($this->currentStep, $allSteps);
        
        if ($currentIndex !== false && isset($allSteps[$currentIndex + 1])) {
            $this->currentStep = $allSteps[$currentIndex + 1];
        } else {
            $this->completeOnboarding();
        }
    }

    public function goToStep(string $step): void
    {
        $allSteps = $this->getAllSteps();
        if (in_array($step, $allSteps)) {
            $this->currentStep = $step;
        }
    }

    public function completeOnboarding(): void
    {
        $this->completedAt = now()->toDateTimeImmutable();
    }

    public function isCompleted(): bool
    {
        return $this->completedAt !== null;
    }

    public function getProgressPercentage(): int
    {
        $allSteps = $this->getAllSteps();
        if (empty($allSteps)) {
            return 0;
        }
        
        $totalSteps = count($allSteps);
        $completedCount = count($this->completedSteps);
        
        return (int) round(($completedCount / $totalSteps) * 100);
    }

    public function canSkip(): bool
    {
        return !$this->isRequiredStep($this->currentStep);
    }

    private function isRequiredStep(string $step): bool
    {
        $requiredSteps = ['productos', 'medicamentos', 'platos', 'categorias'];
        return in_array($step, $requiredSteps);
    }

    private function getAllSteps(): array
    {
        // Esto se obtendría del tenant/rubro en una implementación real
        return ['productos', 'caja', 'ventas', 'configuracion'];
    }

    public static function getStepsForRubro(string $rubro): array
    {
        $steps = [
            'retail' => ['productos', 'caja', 'ventas'],
            'farmacia' => ['medicamentos', 'proveedores', 'obras_sociales'],
            'restaurante' => ['platos', 'insumos', 'proveedores'],
            'ferreteria' => ['categorias', 'productos', 'clientes'],
            'moda' => ['productos', 'variantes', 'temporada'],
            'distribuidora' => ['productos', 'clientes', 'rutas'],
            'manufactura' => ['materia_prima', 'productos_terminados', 'recetas'],
        ];

        return $steps[$rubro] ?? $steps['retail'];
    }

    public function getStepConfig(): array
    {
        $configs = [
            'productos' => [
                'title' => 'Carga tus Productos',
                'description' => 'Agrega tus productos manualmente o importa desde Excel',
                'icon' => 'package',
                'actions' => ['create', 'import', 'skip'],
            ],
            'medicamentos' => [
                'title' => 'Carga de Medicamentos',
                'description' => 'Registra tus medicamentos con lotes y vencimientos',
                'icon' => 'pill',
                'actions' => ['create', 'import', 'skip'],
            ],
            'platos' => [
                'title' => 'Define tus Platos',
                'description' => 'Crea tu menú con recetas e insumos',
                'icon' => 'utensils',
                'actions' => ['create', 'skip'],
            ],
            'categorias' => [
                'title' => 'Organiza por Categorías',
                'description' => 'Crea la estructura de categorías para tus productos',
                'icon' => 'folder-tree',
                'actions' => ['create', 'skip'],
            ],
            'caja' => [
                'title' => 'Configura tu Caja',
                'description' => 'Define formas de pago y configuración inicial',
                'icon' => 'cash-register',
                'actions' => ['configure', 'skip'],
            ],
            'ventas' => [
                'title' => 'Realiza tu Primera Venta',
                'description' => 'Prueba el sistema haciendo una venta de prueba',
                'icon' => 'shopping-cart',
                'actions' => ['demo', 'skip'],
            ],
            'proveedores' => [
                'title' => 'Registra Proveedores',
                'description' => 'Agrega tus proveedores habituales',
                'icon' => 'truck',
                'actions' => ['create', 'import', 'skip'],
            ],
            'obras_sociales' => [
                'title' => 'Obras Sociales',
                'description' => 'Configura las obras sociales que aceptás',
                'icon' => 'heart-pulse',
                'actions' => ['create', 'skip'],
            ],
            'insumos' => [
                'title' => 'Insumos',
                'description' => 'Registra los insumos para tus recetas',
                'icon' => 'carrot',
                'actions' => ['create', 'import', 'skip'],
            ],
            'clientes' => [
                'title' => 'Tus Clientes',
                'description' => 'Carga tu base de clientes',
                'icon' => 'users',
                'actions' => ['create', 'import', 'skip'],
            ],
            'variantes' => [
                'title' => 'Tallas y Colores',
                'description' => 'Configura las variantes para tus productos',
                'icon' => 'palette',
                'actions' => ['create', 'skip'],
            ],
            'temporada' => [
                'title' => 'Temporadas',
                'description' => 'Define temporadas para colecciones',
                'icon' => 'calendar',
                'actions' => ['create', 'skip'],
            ],
            'rutas' => [
                'title' => 'Rutas de Entrega',
                'description' => 'Configura las rutas de distribución',
                'icon' => 'map-pin',
                'actions' => ['create', 'skip'],
            ],
            'materia_prima' => [
                'title' => 'Materia Prima',
                'description' => 'Registra tus materias primas',
                'icon' => 'factory',
                'actions' => ['create', 'import', 'skip'],
            ],
            'productos_terminados' => [
                'title' => 'Productos Terminados',
                'description' => 'Define tus productos manufacturados',
                'icon' => 'box',
                'actions' => ['create', 'skip'],
            ],
            'recetas' => [
                'title' => 'Recetas / BOM',
                'description' => 'Crea las recetas de producción',
                'icon' => 'scroll',
                'actions' => ['create', 'skip'],
            ],
            'configuracion' => [
                'title' => 'Configuración Final',
                'description' => 'Revisa y completa la configuración',
                'icon' => 'settings',
                'actions' => ['review', 'complete'],
            ],
        ];

        return $configs[$this->currentStep] ?? $configs['configuracion'];
    }
}
