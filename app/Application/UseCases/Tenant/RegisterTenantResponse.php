<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tenant;

use App\Domain\Entities\Tenant;

final class RegisterTenantResponse
{
    private function __construct(
        private readonly bool $success,
        private readonly ?Tenant $tenant = null,
        private readonly ?string $error = null
    ) {}

    public static function success(Tenant $tenant): self
    {
        return new self(true, $tenant, null);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }

    public function isSuccess(): bool { return $this->success; }
    public function tenant(): ?Tenant { return $this->tenant; }
    public function error(): ?string { return $this->error; }
}
