<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

use App\Domain\RepositoryInterfaces\OnboardingRepositoryInterface;
use App\Domain\ValueObjects\TenantId;

final class GetOnboardingStatusUseCase
{
    public function __construct(
        private readonly OnboardingRepositoryInterface $onboardingRepository
    ) {}

    public function execute(GetOnboardingStatusRequest $request): GetOnboardingStatusResponse
    {
        $tenantId = TenantId::fromString($request->tenantId);
        $progress = $this->onboardingRepository->findByTenantId($tenantId);
        
        if (!$progress) {
            return GetOnboardingStatusResponse::notStarted();
        }
        
        return GetOnboardingStatusResponse::success(
            onboardingId: $progress->id(),
            currentStep: $progress->currentStep(),
            completedSteps: $progress->completedSteps(),
            progressPercentage: $progress->getProgressPercentage(),
            isCompleted: $progress->isCompleted(),
            stepConfig: $progress->isCompleted() ? null : $progress->getStepConfig(),
            startedAt: $progress->startedAt()->format('Y-m-d H:i:s'),
            completedAt: $progress->completedAt()?->format('Y-m-d H:i:s')
        );
    }
}
