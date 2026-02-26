<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\TenantId;
use App\Domain\ValueObjects\TenantSlug;
use DateTimeImmutable;

final class Tenant
{
    private array $settings;

    private function __construct(
        private readonly TenantId $id,
        private TenantSlug $slug,
        private string $name,
        private string $rubro,
        private string $database,
        private string $plan,
        private ?string $email = null,
        private ?DateTimeImmutable $trialEndsAt = null,
        private ?DateTimeImmutable $subscribedAt = null,
        private string $status = 'pending',
        array $settings = []
    ) {
        $this->settings = $settings ?: self::getDefaultSettingsForRubro($rubro);
    }

    public static function create(
        TenantSlug $slug,
        string $name,
        string $rubro,
        string $database,
        string $plan,
        ?string $email = null
    ): self {
        return new self(
            id: TenantId::generate(),
            slug: $slug,
            name: $name,
            rubro: $rubro,
            database: $database,
            plan: $plan,
            email: $email,
            trialEndsAt: now()->addDays(14)->toDateTimeImmutable(),
            status: 'pending'
        );
    }

    public static function reconstitute(
        TenantId $id,
        TenantSlug $slug,
        string $name,
        string $rubro,
        string $database,
        string $plan,
        ?string $email,
        ?DateTimeImmutable $trialEndsAt,
        ?DateTimeImmutable $subscribedAt,
        string $status,
        array $settings
    ): self {
        return new self(
            id: $id,
            slug: $slug,
            name: $name,
            rubro: $rubro,
            database: $database,
            plan: $plan,
            email: $email,
            trialEndsAt: $trialEndsAt,
            subscribedAt: $subscribedAt,
            status: $status,
            settings: $settings
        );
    }

    // Getters
    public function id(): TenantId { return $this->id; }
    public function slug(): TenantSlug { return $this->slug; }
    public function name(): string { return $this->name; }
    public function rubro(): string { return $this->rubro; }
    public function database(): string { return $this->database; }
    public function plan(): string { return $this->plan; }
    public function email(): ?string { return $this->email; }
    public function trialEndsAt(): ?DateTimeImmutable { return $this->trialEndsAt; }
    public function subscribedAt(): ?DateTimeImmutable { return $this->subscribedAt; }
    public function status(): string { return $this->status; }
    public function settings(): array { return $this->settings; }

    // Domain methods
    public function activate(): void
    {
        $this->status = 'active';
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
    }

    public function markAsSubscribed(): void
    {
        $this->subscribedAt = now()->toDateTimeImmutable();
        $this->trialEndsAt = null;
    }

    public function isOnTrial(): bool
    {
        return $this->trialEndsAt !== null && $this->trialEndsAt > now();
    }

    public function hasExpiredTrial(): bool
    {
        return $this->trialEndsAt !== null && $this->trialEndsAt <= now();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getUrl(): string
    {
        $domain = config('app.domain', 'inventariosmart.app');
        return "https://{$this->slug}.{$domain}";
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    public function updatePlan(string $plan): void
    {
        $this->plan = $plan;
    }

    public function updateSettings(array $settings): void
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    private static function getDefaultSettingsForRubro(string $rubro): array
    {
        $defaults = [
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

        return $defaults[$rubro] ?? $defaults['retail'];
    }
}
