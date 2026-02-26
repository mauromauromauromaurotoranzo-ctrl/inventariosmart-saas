<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

final class StartOnboardingRequest
{
    public function __construct(
        public readonly string $tenantId
    ) {}
}
