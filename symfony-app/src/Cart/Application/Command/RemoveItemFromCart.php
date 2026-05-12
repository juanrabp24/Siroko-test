<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

final class RemoveItemFromCart
{
    public function __construct(
        private readonly string $cartId,
        private readonly string $productId,
    ) {}

    public function cartId(): string
    {
        return $this->cartId;
    }

    public function productId(): string
    {
        return $this->productId;
    }
}
