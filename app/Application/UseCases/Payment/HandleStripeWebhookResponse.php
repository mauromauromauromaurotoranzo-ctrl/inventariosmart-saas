<?php

declare(strict_types=1);

namespace App\Application\UseCases\Payment;

final class HandleStripeWebhookResponse
{
    private function __construct(
        public readonly bool $success,
        public readonly ?string $error = null
    ) {}

    public static function success(): self
    {
        return new self(success: true);
    }

    public static function error(string $message): self
    {
        return new self(
            success: false,
            error: $message
        );
    }
}
