<?php

declare(strict_types=1);

namespace App\Application\UseCases\Payment;

final class CreateCheckoutSessionRequest
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $plan // starter, professional, business
    ) {}
}
