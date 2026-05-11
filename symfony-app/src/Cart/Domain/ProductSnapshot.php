<?php

declare(strict_types=1);

namespace App\Cart\Domain;

use App\Shared\Domain\Money;
use InvalidArgumentException;

final class ProductSnapshot
{
    private function __construct(
        private readonly string $productId,
        private readonly string $name,
        private readonly Money $price,
    ) {}

    public static function create(string $productId, string $name, Money $price): self
    {
        if (empty(trim($productId))) {
            throw new InvalidArgumentException('El id del producto no puede estar vacío');
        }

        if (empty(trim($name))) {
            throw new InvalidArgumentException('El nombre del producto no puede estar vacío');
        }

        return new self($productId, $name, $price);
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function equals(self $other): bool
    {
        return $this->productId === $other->productId
            && $this->name === $other->name
            && $this->price->equals($other->price);
    }
}
