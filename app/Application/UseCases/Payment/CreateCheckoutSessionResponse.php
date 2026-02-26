<?php

declare(strict_types=1);

namespace App\Application\UseCases\Payment;

final class CreateCheckoutSessionResponse
{
    private function __construct(
        public readonly bool $success,
        public readonly ?string $sessionId = null,
        public readonly ?string $checkoutUrl = null,
        public readonly ?string $error = null
    ) {}

    public static function success(string $sessionId, string $checkoutUrl): self
    {
        return new self(
            success: true,
            sessionId: $sessionId,
            checkoutUrl: $checkoutUrl
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
