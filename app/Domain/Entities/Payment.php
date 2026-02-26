<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\TenantId;
use DateTimeImmutable;

final class Payment
{
    private function __construct(
        private readonly string $id,
        private readonly TenantId $tenantId,
        private string $stripePaymentIntentId,
        private float $amount,
        private string $currency,
        private string $status, // pending, succeeded, failed, refunded
        private string $plan, // starter, professional, business
        private ?string $stripeInvoiceId = null,
        private ?string $receiptUrl = null,
        private ?DateTimeImmutable $paidAt = null,
        private ?DateTimeImmutable $refundedAt = null,
        private ?string $failureMessage = null,
        private readonly DateTimeImmutable $createdAt
    ) {}

    public static function create(
        TenantId $tenantId,
        string $stripePaymentIntentId,
        float $amount,
        string $currency,
        string $plan
    ): self {
        return new self(
            id: uniqid('pay_', true),
            tenantId: $tenantId,
            stripePaymentIntentId: $stripePaymentIntentId,
            amount: $amount,
            currency: $currency,
            status: 'pending',
            plan: $plan,
            createdAt: now()->toDateTimeImmutable()
        );
    }

    public static function reconstitute(
        string $id,
        TenantId $tenantId,
        string $stripePaymentIntentId,
        float $amount,
        string $currency,
        string $status,
        string $plan,
        ?string $stripeInvoiceId,
        ?string $receiptUrl,
        ?DateTimeImmutable $paidAt,
        ?DateTimeImmutable $refundedAt,
        ?string $failureMessage,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            id: $id,
            tenantId: $tenantId,
            stripePaymentIntentId: $stripePaymentIntentId,
            amount: $amount,
            currency: $currency,
            status: $status,
            plan: $plan,
            stripeInvoiceId: $stripeInvoiceId,
            receiptUrl: $receiptUrl,
            paidAt: $paidAt,
            refundedAt: $refundedAt,
            failureMessage: $failureMessage,
            createdAt: $createdAt
        );
    }

    // Getters
    public function id(): string { return $this->id; }
    public function tenantId(): TenantId { return $this->tenantId; }
    public function stripePaymentIntentId(): string { return $this->stripePaymentIntentId; }
    public function amount(): float { return $this->amount; }
    public function currency(): string { return $this->currency; }
    public function status(): string { return $this->status; }
    public function plan(): string { return $this->plan; }
    public function stripeInvoiceId(): ?string { return $this->stripeInvoiceId; }
    public function receiptUrl(): ?string { return $this->receiptUrl; }
    public function paidAt(): ?DateTimeImmutable { return $this->paidAt; }
    public function refundedAt(): ?DateTimeImmutable { return $this->refundedAt; }
    public function failureMessage(): ?string { return $this->failureMessage; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }

    // Domain methods
    public function markAsSucceeded(string $stripeInvoiceId, string $receiptUrl): void
    {
        $this->status = 'succeeded';
        $this->stripeInvoiceId = $stripeInvoiceId;
        $this->receiptUrl = $receiptUrl;
        $this->paidAt = now()->toDateTimeImmutable();
    }

    public function markAsFailed(string $failureMessage): void
    {
        $this->status = 'failed';
        $this->failureMessage = $failureMessage;
    }

    public function markAsRefunded(): void
    {
        $this->status = 'refunded';
        $this->refundedAt = now()->toDateTimeImmutable();
    }

    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}
