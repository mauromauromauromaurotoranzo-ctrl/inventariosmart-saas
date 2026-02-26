<?php

declare(strict_types=1);

namespace App\Domain\RepositoryInterfaces;

use App\Domain\Entities\Tenant;
use App\Domain\ValueObjects\TenantId;
use App\Domain\ValueObjects\TenantSlug;

interface TenantRepositoryInterface
{
    public function findById(TenantId $id): ?Tenant;
    
    public function findBySlug(TenantSlug $slug): ?Tenant;
    
    public function findByDatabase(string $database): ?Tenant;
    
    public function save(Tenant $tenant): void;
    
    public function delete(Tenant $tenant): void;
    
    public function existsBySlug(TenantSlug $slug): bool;
    
    public function existsByDatabase(string $database): bool;
    
    /**
     * @return Tenant[]
     */
    public function findAllActive(): array;
    
    /**
     * @return Tenant[]
     */
    public function findExpiredTrials(): array;
}
