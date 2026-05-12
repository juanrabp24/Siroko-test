<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command;

final class CreateProduct
{
    public function __construct(
        private readonly string $productId,
        private readonly string $name,
        private readonly int $price,
        private readonly int $stock,
    ) {}

    public function productId(): string
    {
        return $this->productId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): int
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }
}
