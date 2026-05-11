<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

use InvalidArgumentException;

final class ProductName
{
    private function __construct(
        private readonly string $value,
    ) {}

    public static function create(string $value): self
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('El nombre del producto no puede estar vacío');
        }

        if (strlen(trim($value)) < 2) {
            throw new InvalidArgumentException('El nombre del producto debe tener al menos 2 caracteres');
        }

        if (strlen(trim($value)) > 255) {
            throw new InvalidArgumentException('El nombre del producto no puede superar los 255 caracteres');
        }

        return new self(trim($value));
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
