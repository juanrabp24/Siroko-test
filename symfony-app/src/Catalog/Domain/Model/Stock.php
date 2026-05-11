<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

use InvalidArgumentException;
use DomainException;

final class Stock
{
    private function __construct(
        private readonly int $value,
    ) {}

    public static function create(int $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('El stock no puede ser negativo');
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function isAvailable(): bool
    {
        return $this->value > 0;
    }

    public function decrease(int $units): self
    {
        if ($units > $this->value) {
            throw new DomainException(
                sprintf('Stock insuficiente, disponible: %d, solicitado: %d', $this->value, $units)
            );
        }

        return new self($this->value - $units);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
