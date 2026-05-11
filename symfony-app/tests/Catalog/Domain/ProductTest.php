<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductId;
use App\Catalog\Domain\Model\ProductName;
use App\Catalog\Domain\Model\Stock;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    private function makeProduct(int $stock = 10): Product
    {
        return Product::create(
            ProductId::create('prod-1'),
            ProductName::create('Camiseta térmica'),
            Money::create(1250),
            Stock::create($stock),
        );
    }

    public function testCreacionValida(): void
    {
        $product = $this->makeProduct();

        $this->assertSame('prod-1', $product->id()->value());
        $this->assertSame('Camiseta térmica', $product->name()->value());
        $this->assertSame(1250, $product->price()->amount());
        $this->assertSame(10, $product->stock()->value());
    }

    public function testUpdateStock(): void
    {
        $product = $this->makeProduct();
        $product->updateStock(Stock::create(50));

        $this->assertSame(50, $product->stock()->value());
    }

    public function testDecreaseStock(): void
    {
        $product = $this->makeProduct(10);
        $product->decreaseStock(3);

        $this->assertSame(7, $product->stock()->value());
    }

    public function testDecreaseStockInsuficiente(): void
    {
        $this->expectException(DomainException::class);

        $product = $this->makeProduct(2);
        $product->decreaseStock(5);
    }

    public function testIsAvailable(): void
    {
        $this->assertTrue($this->makeProduct(1)->isAvailable());
    }

    public function testNoDisponibleConStockCero(): void
    {
        $this->assertFalse($this->makeProduct(0)->isAvailable());
    }
}
