<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application\Query;

use App\Catalog\Application\Query\GetAllProducts;
use App\Catalog\Application\Query\GetAllProductsHandler;
use App\Catalog\Application\Query\GetProductByIdResponse;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductId;
use App\Catalog\Domain\Model\ProductName;
use App\Catalog\Domain\Model\Stock;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Shared\Domain\Money;
use PHPUnit\Framework\TestCase;

final class GetAllProductsHandlerTest extends TestCase
{
    private function makeProduct(
        string $id = 'prod-1',
        string $name = 'Camiseta',
        int $price = 150,
        int $stock = 10,
    ): Product {
        return Product::create(
            ProductId::create($id),
            ProductName::create($name),
            Money::create($price),
            Stock::create($stock),
        );
    }

    public function testDevuelveListaDeProductos(): void
    {
        $repo = $this->createStub(ProductRepositoryInterface::class);
        $repo->method('findAvailable')->willReturn([
            $this->makeProduct('prod-1', 'Camiseta', 150, 10),
            $this->makeProduct('prod-2', 'Pantalon', 200, 5),
        ]);

        $result = (new GetAllProductsHandler($repo))(new GetAllProducts());

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(GetProductByIdResponse::class, $result);
        $this->assertSame('prod-1', $result[0]->productId);
        $this->assertSame('Camiseta', $result[0]->name);
        $this->assertSame(150, $result[0]->price);
        $this->assertSame(10, $result[0]->stock);
        $this->assertTrue($result[0]->available);
    }

    public function testDevuelveArrayVacioSiNoHayProductos(): void
    {
        $repo = $this->createStub(ProductRepositoryInterface::class);
        $repo->method('findAvailable')->willReturn([]);

        $result = (new GetAllProductsHandler($repo))(new GetAllProducts());

        $this->assertSame([], $result);
    }
}
