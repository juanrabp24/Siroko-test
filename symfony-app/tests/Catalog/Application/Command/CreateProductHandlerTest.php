<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application\Command;

use App\Catalog\Application\Command\CreateProduct;
use App\Catalog\Application\Command\CreateProductHandler;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateProductHandlerTest extends TestCase
{
    private function makeCommand(
        string $productId = 'prod-1',
        string $name = 'Camiseta',
        int $price = 150,
        int $stock = 10,
    ): CreateProduct {
        return new CreateProduct($productId, $name, $price, $stock);
    }

    public function testCreaProductoYLoGuarda(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(function (Product $product): bool {
                return $product->id()->value() === 'prod-1'
                    && $product->name()->value() === 'Camiseta'
                    && $product->price()->amount() === 150
                    && $product->stock()->value() === 10;
            }));

        (new CreateProductHandler($repo))($this->makeCommand());
    }

    public function testProductoEstaDisponibleSiTieneStock(): void
    {
        $savedProduct = null;

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->willReturnCallback(function (Product $product) use (&$savedProduct): void {
                $savedProduct = $product;
            });

        (new CreateProductHandler($repo))($this->makeCommand(stock: 5));

        $this->assertTrue($savedProduct->isAvailable());
    }

    public function testProductoNoDisponibleConStockCero(): void
    {
        $savedProduct = null;

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->willReturnCallback(function (Product $product) use (&$savedProduct): void {
                $savedProduct = $product;
            });

        (new CreateProductHandler($repo))($this->makeCommand(stock: 0));

        $this->assertFalse($savedProduct->isAvailable());
    }
}
