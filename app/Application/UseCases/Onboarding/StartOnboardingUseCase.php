<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

use App\Domain\Entities\OnboardingProgress;
use App\Domain\Entities\Tenant;
use App\Domain\RepositoryInterfaces\OnboardingRepositoryInterface;
use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Domain\ValueObjects\TenantId;

final class StartOnboardingUseCase
{
    public function __construct(
        private readonly OnboardingRepositoryInterface $onboardingRepository,
        private readonly TenantRepositoryInterface $tenantRepository
    ) {}

    public function execute(StartOnboardingRequest $request): StartOnboardingResponse
    {
        $tenantId = TenantId::fromString($request->tenantId);
        
        // Verificar que el tenant existe
        $tenant = $this->tenantRepository->findById($tenantId);
        if (!$tenant) {
            return StartOnboardingResponse::error('Tenant no encontrado');
        }
        
        // Verificar que no existe onboarding previo
        $existing = $this->onboardingRepository->findByTenantId($tenantId);
        if ($existing) {
            return StartOnboardingResponse::success(
                onboardingId: $existing->id(),
                currentStep: $existing->currentStep(),
                progressPercentage: $existing->getProgressPercentage(),
                stepConfig: $existing->getStepConfig()
            );
        }
        
        // Crear nuevo onboarding
        $progress = OnboardingProgress::start($tenantId, $tenant->rubro());
        $this->onboardingRepository->save($progress);
        
        return StartOnboardingResponse::success(
            onboardingId: $progress->id(),
            currentStep: $progress->currentStep(),
            progressPercentage: 0,
            stepConfig: $progress->getStepConfig()
        );
    }
}
