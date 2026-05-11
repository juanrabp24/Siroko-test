<?php

declare(strict_types=1);

namespace App\Cart\Domain\Model;

use InvalidArgumentException;

final class Quantity
{
    private function __construct(
        private readonly int $value,
    ) {}

    public static function create(int $value): self
    {
        if ($value <= 0) {
            throw new InvalidArgumentException(
                sprintf('La cantidad debe ser mayor que cero, recibido: %d', $value)
            );
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
