<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tenant;

final class RegisterTenantRequest
{
    public function __construct(
        private readonly string $name,
        private readonly string $slug,
        private readonly string $rubro,
        private readonly string $plan,
        private readonly ?string $email = null
    ) {}

    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function rubro(): string { return $this->rubro; }
    public function plan(): string { return $this->plan; }
    public function email(): ?string { return $this->email; }
}
