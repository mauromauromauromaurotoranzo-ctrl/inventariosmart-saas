<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Subscription;
use App\Domain\RepositoryInterfaces\SubscriptionRepositoryInterface;
use App\Domain\ValueObjects\TenantId;
use App\Models\Subscription as SubscriptionModel;
use DateTimeImmutable;

final class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function findById(string $id): ?Subscription
    {
        $model = SubscriptionModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByTenantId(TenantId $tenantId): ?Subscription
    {
        $model = SubscriptionModel::where('tenant_id', $tenantId->value())->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findByStripeSubscriptionId(string $stripeSubscriptionId): ?Subscription
    {
        $model = SubscriptionModel::where('stripe_subscription_id', $stripeSubscriptionId)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function save(Subscription $subscription): void
    {
        SubscriptionModel::updateOrCreate(
            ['id' => $subscription->id()],
            [
                'tenant_id' => $subscription->tenantId()->value(),
                'stripe_subscription_id' => $subscription->stripeSubscriptionId(),
                'stripe_customer_id' => $subscription->stripeCustomerId(),
                'plan' => $subscription->plan(),
                'status' => $subscription->status(),
                'current_period_start' => $subscription->currentPeriodStart(),
                'current_period_end' => $subscription->currentPeriodEnd(),
                'canceled_at' => $subscription->canceledAt(),
            ]
        );
    }

    public function delete(Subscription $subscription): void
    {
        SubscriptionModel::destroy($subscription->id());
    }

    public function findActive(): array
    {
        return SubscriptionModel::where('status', 'active')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->toArray();
    }

    public function findExpiringSoon(int $days = 3): array
    {
        return SubscriptionModel::where('status', 'active')
            ->whereNotNull('current_period_end')
            ->whereDate('current_period_end', '<=', now()->addDays($days))
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->toArray();
    }

    private function toEntity(SubscriptionModel $model): Subscription
    {
        return Subscription::reconstitute(
            id: $model->id,
            tenantId: TenantId::fromString($model->tenant_id),
            stripeSubscriptionId: $model->stripe_subscription_id,
            stripeCustomerId: $model->stripe_customer_id,
            plan: $model->plan,
            status: $model->status,
            currentPeriodStart: $model->current_period_start ? new DateTimeImmutable($model->current_period_start) : null,
            currentPeriodEnd: $model->current_period_end ? new DateTimeImmutable($model->current_period_end) : null,
            canceledAt: $model->canceled_at ? new DateTimeImmutable($model->canceled_at) : null,
            createdAt: new DateTimeImmutable($model->created_at)
        );
    }
}
