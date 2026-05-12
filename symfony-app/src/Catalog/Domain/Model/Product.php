<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;


use App\Shared\Domain\Money;

final class Product
{
    private function __construct(
        private readonly ProductId $id,
        private readonly ProductName $name,
        private readonly Money $price,
        private Stock $stock,
    ) {}

    public static function create(
        ProductId $id,
        ProductName $name,
        Money $price,
        Stock $stock,
    ): self {
        return new self($id, $name, $price, $stock);
    }

    public static function reconstitute(ProductId $id, ProductName $name, Money $price, Stock $stock): self
    {
        return new self($id, $name, $price, $stock);
    }

    public function updateStock(Stock $stock): void
    {
        $this->stock = $stock;
    }

    public function decreaseStock(int $units): void
    {
        $this->stock = $this->stock->decrease($units);
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function stock(): Stock
    {
        return $this->stock;
    }

    public function isAvailable(): bool
    {
        return $this->stock->isAvailable();
    }
}
