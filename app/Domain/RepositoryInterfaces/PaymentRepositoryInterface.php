<?php

declare(strict_types=1);

namespace App\Domain\RepositoryInterfaces;

use App\Domain\Entities\Payment;
use App\Domain\ValueObjects\TenantId;

interface PaymentRepositoryInterface
{
    public function findById(string $id): ?Payment;
    
    public function findByStripePaymentIntentId(string $paymentIntentId): ?Payment;
    
    /**
     * @return Payment[]
     */
    public function findByTenantId(TenantId $tenantId): array;
    
    public function save(Payment $payment): void;
    
    public function delete(Payment $payment): void;
}
