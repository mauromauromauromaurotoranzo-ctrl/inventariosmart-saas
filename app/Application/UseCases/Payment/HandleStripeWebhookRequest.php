<?php

declare(strict_types=1);

namespace App\Application\UseCases\Payment;

final class HandleStripeWebhookRequest
{
    public function __construct(
        public readonly string $eventType,
        public readonly array $payload
    ) {}
}
