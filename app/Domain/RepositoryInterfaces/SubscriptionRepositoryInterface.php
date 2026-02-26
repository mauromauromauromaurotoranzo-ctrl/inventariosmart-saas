<?php

declare(strict_types=1);

namespace App\Domain\RepositoryInterfaces;

use App\Domain\Entities\Subscription;
use App\Domain\ValueObjects\TenantId;

interface SubscriptionRepositoryInterface
{
    public function findById(string $id): ?Subscription;
    
    public function findByTenantId(TenantId $tenantId): ?Subscription;
    
    public function findByStripeSubscriptionId(string $stripeSubscriptionId): ?Subscription;
    
    public function save(Subscription $subscription): void;
    
    public function delete(Subscription $subscription): void;
    
    /**
     * @return Subscription[]
     */
    public function findActive(): array;
    
    /**
     * @return Subscription[]
     */
    public function findExpiringSoon(int $days = 3): array;
}
