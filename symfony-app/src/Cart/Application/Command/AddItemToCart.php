<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

final class AddItemToCart
{
    public function __construct(
        private readonly string $cartId,
        private readonly string $userId,
        private readonly string $productId,
        private readonly string $productName,
        private readonly int $productPrice,
        private readonly int $quantity,
    ) {}

    public function cartId(): string
    {
        return $this->cartId;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function productPrice(): int
    {
        return $this->productPrice;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
