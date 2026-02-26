<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

final class StartOnboardingResponse
{
    private function __construct(
        public readonly bool $success,
        public readonly ?string $onboardingId = null,
        public readonly ?string $currentStep = null,
        public readonly int $progressPercentage = 0,
        public readonly ?array $stepConfig = null,
        public readonly ?string $error = null
    ) {}

    public static function success(
        string $onboardingId,
        string $currentStep,
        int $progressPercentage,
        array $stepConfig
    ): self {
        return new self(
            success: true,
            onboardingId: $onboardingId,
            currentStep: $currentStep,
            progressPercentage: $progressPercentage,
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
