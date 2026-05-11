<?php

declare(strict_types=1);

namespace App\Order\Domain\Model;

use InvalidArgumentException;

final class OrderStatus
{
    private const PENDING = 'pending';
    private const PAID = 'paid';
    private const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::PENDING,
        self::PAID,
        self::CANCELLED,
    ];

    private function __construct(
        private readonly string $value,
    ) {}

    public static function create(string $value): self
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Estado no válido: %s', $value)
            );
        }

        return new self($value);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
