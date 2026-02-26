<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\OnboardingProgress;
use App\Domain\RepositoryInterfaces\OnboardingRepositoryInterface;
use App\Domain\ValueObjects\TenantId;
use App\Models\OnboardingProgress as OnboardingModel;
use DateTimeImmutable;

class EloquentOnboardingRepository implements OnboardingRepositoryInterface
{
    public function findByTenantId(TenantId $tenantId): ?OnboardingProgress
    {
        $model = OnboardingModel::where('tenant_id', $tenantId->value())->first();
        
        if (!$model) {
            return null;
        }
        
        return $this->toEntity($model);
    }

    public function findById(string $id): ?OnboardingProgress
    {
        $model = OnboardingModel::find($id);
        
        if (!$model) {
            return null;
        }
        
        return $this->toEntity($model);
    }

    public function save(OnboardingProgress $progress): void
    {
        OnboardingModel::updateOrCreate(
            ['id' => $progress->id()],
            [
                'tenant_id' => $progress->tenantId()->value(),
                'current_step' => $progress->currentStep(),
                'completed_steps' => json_encode($progress->completedSteps()),
                'step_data' => json_encode($progress->stepData()),
                'started_at' => $progress->startedAt(),
                'completed_at' => $progress->completedAt(),
            ]
        );
    }

    public function delete(OnboardingProgress $progress): void
    {
        OnboardingModel::destroy($progress->id());
    }

    private function toEntity(OnboardingModel $model): OnboardingProgress
    {
        return OnboardingProgress::reconstitute(
            id: $model->id,
            tenantId: TenantId::fromString($model->tenant_id),
            currentStep: $model->current_step,
            completedSteps: json_decode($model->completed_steps ?? '[]', true),
            startedAt: new DateTimeImmutable($model->started_at),
            completedAt: $model->completed_at ? new DateTimeImmutable($model->completed_at) : null,
            stepData: json_decode($model->step_data ?? '{}', true)
        );
    }
}
