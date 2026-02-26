<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class TenantSlug
{
    private function __construct(
        private readonly string $value
    ) {
        if (!preg_match('/^[a-z0-9-]+$/', $this->value)) {
            throw new InvalidArgumentException('Slug must contain only lowercase letters, numbers, and hyphens');
        }

        if (strlen($this->value) < 3 || strlen($this->value) > 50) {
            throw new InvalidArgumentException('Slug must be between 3 and 50 characters');
        }
    }

    public static function fromString(string $value): self
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        return new self($slug);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
