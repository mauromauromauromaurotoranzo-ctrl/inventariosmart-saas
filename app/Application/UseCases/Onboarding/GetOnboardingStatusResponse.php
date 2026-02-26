<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

final class GetOnboardingStatusResponse
{
    private function __construct(
        public readonly bool $success,
        public readonly bool $hasStarted,
        public readonly ?string $onboardingId = null,
        public readonly ?string $currentStep = null,
        public readonly array $completedSteps = [],
        public readonly int $progressPercentage = 0,
        public readonly bool $isCompleted = false,
        public readonly ?array $stepConfig = null,
        public readonly ?string $startedAt = null,
        public readonly ?string $completedAt = null
    ) {}

    public static function success(
        string $onboardingId,
        string $currentStep,
        array $completedSteps,
        int $progressPercentage,
        bool $isCompleted,
        ?array $stepConfig,
        string $startedAt,
        ?string $completedAt
    ): self {
        return new self(
            success: true,
            hasStarted: true,
            onboardingId: $onboardingId,
            currentStep: $currentStep,
            completedSteps: $completedSteps,
            progressPercentage: $progressPercentage,
            isCompleted: $isCompleted,
            stepConfig: $stepConfig,
            startedAt: $startedAt,
            completedAt: $completedAt
        );
    }

    public static function notStarted(): self
    {
        return new self(
            success: true,
            hasStarted: false
        );
    }
}
