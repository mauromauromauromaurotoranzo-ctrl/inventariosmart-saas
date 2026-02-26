<?php

declare(strict_types=1);

namespace App\Application\UseCases\Onboarding;

final class GetOnboardingStatusRequest
{
    public function __construct(
        public readonly string $tenantId
    ) {}
}
