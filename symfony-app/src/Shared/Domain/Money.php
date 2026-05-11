<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use InvalidArgumentException;

final class Money
{
    private const CURRENCY = 'EUR';

    private function __construct(
        private readonly int $amount,
    ) {}

    public static function create(int $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException(
                sprintf('El importe no puede ser negativo, recibido: %d', $amount)
            );
        }

        return new self($amount);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return self::CURRENCY;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function multiply(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('El factor no puede ser negativo');
        }

        return new self($this->amount * $factor);
    }
}
