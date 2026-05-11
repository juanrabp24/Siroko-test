<?php

declare(strict_types=1);

namespace App\Payment\Domain\Model;

use InvalidArgumentException;

final class PaymentStatus
{
    private const PENDING = 'pending';
    private const SUCCESS = 'success';
    private const FAILED = 'failed';

    private const VALID_STATUSES = [
        self::PENDING,
        self::SUCCESS,
        self::FAILED,
    ];

    private function __construct(
        private readonly string $value,
    ) {}

    public static function create(string $value): self
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf('Estado de pago no válido: %s', $value)
            );
        }

        return new self($value);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function success(): self
    {
        return new self(self::SUCCESS);
    }

    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->value === self::SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->value === self::FAILED;
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
