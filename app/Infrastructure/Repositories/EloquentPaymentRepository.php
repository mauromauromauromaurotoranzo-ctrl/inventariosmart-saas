<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Payment;
use App\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use App\Domain\ValueObjects\TenantId;
use App\Models\Payment as PaymentModel;
use DateTimeImmutable;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function findById(string $id): ?Payment
    {
        $model = PaymentModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByStripePaymentIntentId(string $paymentIntentId): ?Payment
    {
        $model = PaymentModel::where('stripe_payment_intent_id', $paymentIntentId)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findByTenantId(TenantId $tenantId): array
    {
        return PaymentModel::where('tenant_id', $tenantId->value())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->toArray();
    }

    public function save(Payment $payment): void
    {
        PaymentModel::updateOrCreate(
            ['id' => $payment->id()],
            [
                'tenant_id' => $payment->tenantId()->value(),
                'stripe_payment_intent_id' => $payment->stripePaymentIntentId(),
                'amount' => $payment->amount(),
                'currency' => $payment->currency(),
                'status' => $payment->status(),
                'plan' => $payment->plan(),
                'stripe_invoice_id' => $payment->stripeInvoiceId(),
                'receipt_url' => $payment->receiptUrl(),
                'paid_at' => $payment->paidAt(),
                'refunded_at' => $payment->refundedAt(),
                'failure_message' => $payment->failureMessage(),
                'created_at' => $payment->createdAt(),
            ]
        );
    }

    public function delete(Payment $payment): void
    {
        PaymentModel::destroy($payment->id());
    }

    private function toEntity(PaymentModel $model): Payment
    {
        return Payment::reconstitute(
            id: $model->id,
            tenantId: TenantId::fromString($model->tenant_id),
            stripePaymentIntentId: $model->stripe_payment_intent_id,
            amount: (float) $model->amount,
            currency: $model->currency,
            status: $model->status,
            plan: $model->plan,
            stripeInvoiceId: $model->stripe_invoice_id,
            receiptUrl: $model->receipt_url,
            paidAt: $model->paid_at ? new DateTimeImmutable($model->paid_at) : null,
            refundedAt: $model->refunded_at ? new DateTimeImmutable($model->refunded_at) : null,
            failureMessage: $model->failure_message,
            createdAt: new DateTimeImmutable($model->created_at)
        );
    }
}
