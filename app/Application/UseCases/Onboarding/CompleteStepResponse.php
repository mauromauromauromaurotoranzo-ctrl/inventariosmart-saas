<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

final class CompleteStepResponse
{
    private function __construct(
        public readonly bool $success,
        public readonly ?string $onboardingId = null,
        public readonly ?string $currentStep = null,
        public readonly array $completedSteps = [],
        public readonly int $progressPercentage = 0,
        public readonly bool $isCompleted = false,
        public readonly ?array $stepConfig = null,
        public readonly ?string $error = null
    ) {}

    public static function success(
        string $onboardingId,
        string $currentStep,
        array $completedSteps,
        int $progressPercentage,
        bool $isCompleted,
        ?array $stepConfig
    ): self {
        return new self(
            success: true,
            onboardingId: $onboardingId,
            currentStep: $currentStep,
            completedSteps: $completedSteps,
            progressPercentage: $progressPercentage,
            isCompleted: $isCompleted,
            stepConfig: $stepConfig
        );
    }

    public static function error(string $message): self
    {
        return new self(
            success: false,
            error: $message
        );
    }
}
