<?php

declare(strict_types=1);

namespace App\Order\Domain\Model;

use App\Shared\Domain\Money;

final class OrderItem
{
    private function __construct(
        private readonly string $productId,
        private readonly string $productName,
        private readonly Money $unitPrice,
        private readonly int $quantity,
    ) {}

    public static function create(
        string $productId,
        string $productName,
        Money $unitPrice,
        int $quantity,
    ): self {
        return new self($productId, $productName, $unitPrice, $quantity);
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function total(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }
}
