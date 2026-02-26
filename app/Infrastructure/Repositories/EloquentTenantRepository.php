<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Tenant;
use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Domain\ValueObjects\TenantId;
use App\Domain\ValueObjects\TenantSlug;
use App\Models\Tenant as TenantModel;
use DateTimeImmutable;

final class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function findById(TenantId $id): ?Tenant
    {
        $model = TenantModel::find($id->value());
        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(TenantSlug $slug): ?Tenant
    {
        $model = TenantModel::where('slug', $slug->value())->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findByDatabase(string $database): ?Tenant
    {
        $model = TenantModel::where('database', $database)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function save(Tenant $tenant): void
    {
        TenantModel::updateOrCreate(
            ['id' => $tenant->id()->value()],
            [
                'slug' => $tenant->slug()->value(),
                'name' => $tenant->name(),
                'rubro' => $tenant->rubro(),
                'database' => $tenant->database(),
                'plan' => $tenant->plan(),
                'email' => $tenant->email(),
                'trial_ends_at' => $tenant->trialEndsAt(),
                'subscribed_at' => $tenant->subscribedAt(),
                'status' => $tenant->status(),
                'settings' => $tenant->settings(),
            ]
        );
    }

    public function delete(Tenant $tenant): void
    {
        TenantModel::destroy($tenant->id()->value());
    }

    public function existsBySlug(TenantSlug $slug): bool
    {
        return TenantModel::where('slug', $slug->value())->exists();
    }

    public function existsByDatabase(string $database): bool
    {
        return TenantModel::where('database', $database)->exists();
    }

    public function findAllActive(): array
    {
        return TenantModel::where('status', 'active')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->toArray();
    }

    public function findExpiredTrials(): array
    {
        return TenantModel::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->whereNull('subscribed_at')
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->toArray();
    }

    private function toEntity(TenantModel $model): Tenant
    {
        return Tenant::reconstitute(
            id: TenantId::fromString($model->id),
            slug: TenantSlug::fromString($model->slug),
            name: $model->name,
            rubro: $model->rubro,
            database: $model->database,
            plan: $model->plan,
            email: $model->email,
            trialEndsAt: $model->trial_ends_at ? new DateTimeImmutable($model->trial_ends_at) : null,
            subscribedAt: $model->subscribed_at ? new DateTimeImmutable($model->subscribed_at) : null,
            status: $model->status,
            settings: $model->settings ?? []
        );
    }
}
