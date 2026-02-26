<?php

declare(strict_types=1);

namespace App\Domain\RepositoryInterfaces;

use App\Domain\Entities\OnboardingProgress;
use App\Domain\ValueObjects\TenantId;

interface OnboardingRepositoryInterface
{
    public function findByTenantId(TenantId $tenantId): ?OnboardingProgress;
    
    public function save(OnboardingProgress $progress): void;
    
    public function delete(OnboardingProgress $progress): void;
}
