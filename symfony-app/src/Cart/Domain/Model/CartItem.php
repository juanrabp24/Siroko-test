<?php

declare(strict_types=1);

namespace App\Cart\Domain\Model;

use App\Shared\Domain\Money;

final class CartItem
{
    private function __construct(
        private readonly ProductSnapshot $product,
        private Quantity $quantity,
    ) {}

    public static function create(ProductSnapshot $product, Quantity $quantity): self
    {
        return new self($product, $quantity);
    }

    public function updateQuantity(Quantity $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function product(): ProductSnapshot
    {
        return $this->product;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function total(): Money
    {
        return $this->product->price()->multiply($this->quantity->value());
    }
}
