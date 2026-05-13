<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application\Query;

use App\Catalog\Application\Query\GetProductById;
use App\Catalog\Application\Query\GetProductByIdHandler;
use App\Catalog\Application\Query\GetProductByIdResponse;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductId;
use App\Catalog\Domain\Model\ProductName;
use App\Catalog\Domain\Model\Stock;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class GetProductByIdHandlerTest extends TestCase
{
    private function makeProduct(int $stock = 10): Product
    {
        return Product::create(
            ProductId::create('prod-1'),
            ProductName::create('Camiseta'),
            Money::create(150),
            Stock::create($stock),
        );
    }

    public function testDevuelveGetProductByIdResponse(): void
    {
        $repo = $this->createStub(ProductRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makeProduct());

        $response = (new GetProductByIdHandler($repo))(new GetProductById('prod-1'));

        $this->assertInstanceOf(GetProductByIdResponse::class, $response);
        $this->assertSame('prod-1', $response->productId);
        $this->assertSame('Camiseta', $response->name);
        $this->assertSame(150, $response->price);
        $this->assertSame(10, $response->stock);
        $this->assertTrue($response->available);
    }

    public function testDisponibilidadEsFalseConStockCero(): void
    {
        $repo = $this->createStub(ProductRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makeProduct(0));

        $response = (new GetProductByIdHandler($repo))(new GetProductById('prod-1'));

        $this->assertFalse($response->available);
        $this->assertSame(0, $response->stock);
    }

    public function testLanzaExcepcionSiProductoNoEncontrado(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Producto no encontrado');

        $repo = $this->createStub(ProductRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        (new GetProductByIdHandler($repo))(new GetProductById('prod-inexistente'));
    }
}
