<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

final class CompleteStepRequest
{
    public function __construct(
        public readonly string $onboardingId,
        public readonly ?array $stepData = null
    ) {}
}
