<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

use App\Domain\RepositoryInterfaces\OnboardingRepositoryInterface;

final class CompleteStepUseCase
{
    public function __construct(
        private readonly OnboardingRepositoryInterface $onboardingRepository
    ) {}

    public function execute(CompleteStepRequest $request): CompleteStepResponse
    {
        $progress = $this->onboardingRepository->findById($request->onboardingId);
        
        if (!$progress) {
            return CompleteStepResponse::error('Onboarding no encontrado');
        }
        
        if ($progress->isCompleted()) {
            return CompleteStepResponse::error('Onboarding ya completado');
        }
        
        // Completar el paso actual con los datos proporcionados
        $progress->completeCurrentStep($request->stepData ?? []);
        $this->onboardingRepository->save($progress);
        
        return CompleteStepResponse::success(
            onboardingId: $progress->id(),
            currentStep: $progress->currentStep(),
            completedSteps: $progress->completedSteps(),
            progressPercentage: $progress->getProgressPercentage(),
            isCompleted: $progress->isCompleted(),
            stepConfig: $progress->isCompleted() ? null : $progress->getStepConfig()
        );
    }
}
