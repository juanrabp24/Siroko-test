<?php

declare(strict_types=1);

namespace App\Tests\Cart\Application\Query;

use App\Cart\Application\Query\GetCart;
use App\Cart\Application\Query\GetCartHandler;
use App\Cart\Application\Query\GetCartResponse;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Model\ProductSnapshot;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class GetCartHandlerTest extends TestCase
{
    private function makeCartConItem(): Cart
    {
        $cart = Cart::create(CartId::create('cart-1'), 'user-1');
        $cart->addItem(
            ProductSnapshot::create('prod-1', 'Camiseta', Money::create(150)),
            Quantity::create(3),
        );
        return $cart;
    }

    public function testDevuelveGetCartResponse(): void
    {
        $repo = $this->createStub(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makeCartConItem());

        $response = (new GetCartHandler($repo))(new GetCart('cart-1'));

        $this->assertInstanceOf(GetCartResponse::class, $response);
        $this->assertSame('cart-1', $response->cartId);
        $this->assertSame('user-1', $response->userId);
        $this->assertFalse($response->isChecked);
        $this->assertSame(450, $response->total);
    }

    public function testItemsDelResponseContieneCamposCorrectos(): void
    {
        $repo = $this->createStub(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makeCartConItem());

        $response = (new GetCartHandler($repo))(new GetCart('cart-1'));

        $this->assertCount(1, $response->items);
        $item = $response->items[0];
        $this->assertSame('prod-1', $item['productId']);
        $this->assertSame('Camiseta', $item['productName']);
        $this->assertSame(150, $item['unitPrice']);
        $this->assertSame(3, $item['quantity']);
        $this->assertSame(450, $item['total']);
    }

    public function testLanzaExcepcionSiCarritoNoEncontrado(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Carrito no encontrado');

        $repo = $this->createStub(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        (new GetCartHandler($repo))(new GetCart('cart-inexistente'));
    }
}
