<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\TenantId;
use DateTimeImmutable;

final class Subscription
{
    private function __construct(
        private readonly string $id,
        private readonly TenantId $tenantId,
        private string $stripeSubscriptionId,
        private string $stripeCustomerId,
        private string $plan,
        private string $status,
        private ?DateTimeImmutable $currentPeriodStart = null,
        private ?DateTimeImmutable $currentPeriodEnd = null,
        private ?DateTimeImmutable $canceledAt = null,
        private ?DateTimeImmutable $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? now()->toDateTimeImmutable();
    }

    public static function create(
        TenantId $tenantId,
        string $stripeSubscriptionId,
        string $stripeCustomerId,
        string $plan
    ): self {
        return new self(
            id: uniqid('sub_', true),
            tenantId: $tenantId,
            stripeSubscriptionId: $stripeSubscriptionId,
            stripeCustomerId: $stripeCustomerId,
            plan: $plan,
            status: 'active'
        );
    }

    public static function reconstitute(
        string $id,
        TenantId $tenantId,
        string $stripeSubscriptionId,
        string $stripeCustomerId,
        string $plan,
        string $status,
        ?DateTimeImmutable $currentPeriodStart,
        ?DateTimeImmutable $currentPeriodEnd,
        ?DateTimeImmutable $canceledAt,
        ?DateTimeImmutable $createdAt
    ): self {
        return new self(
            id: $id,
            tenantId: $tenantId,
            stripeSubscriptionId: $stripeSubscriptionId,
            stripeCustomerId: $stripeCustomerId,
            plan: $plan,
            status: $status,
            currentPeriodStart: $currentPeriodStart,
            currentPeriodEnd: $currentPeriodEnd,
            canceledAt: $canceledAt,
            createdAt: $createdAt
        );
    }

    // Getters
    public function id(): string { return $this->id; }
    public function tenantId(): TenantId { return $this->tenantId; }
    public function stripeSubscriptionId(): string { return $this->stripeSubscriptionId; }
    public function stripeCustomerId(): string { return $this->stripeCustomerId; }
    public function plan(): string { return $this->plan; }
    public function status(): string { return $this->status; }
    public function currentPeriodStart(): ?DateTimeImmutable { return $this->currentPeriodStart; }
    public function currentPeriodEnd(): ?DateTimeImmutable { return $this->currentPeriodEnd; }
    public function canceledAt(): ?DateTimeImmutable { return $this->canceledAt; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }

    // Domain methods
    public function activate(): void
    {
        $this->status = 'active';
    }

    public function cancel(): void
    {
        $this->status = 'canceled';
        $this->canceledAt = now()->toDateTimeImmutable();
    }

    public function markAsPastDue(): void
    {
        $this->status = 'past_due';
    }

    public function updatePeriod(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        $this->currentPeriodStart = $start;
        $this->currentPeriodEnd = $end;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }
}
