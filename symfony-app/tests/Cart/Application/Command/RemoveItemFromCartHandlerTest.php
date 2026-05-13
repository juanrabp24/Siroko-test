<?php

declare(strict_types=1);

namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\RemoveItemFromCart;
use App\Cart\Application\Command\RemoveItemFromCartHandler;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Model\ProductSnapshot;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class RemoveItemFromCartHandlerTest extends TestCase
{
    private function makeCartConItem(string $productId = 'prod-1'): Cart
    {
        $cart = Cart::create(CartId::create('cart-1'), 'user-1');
        $cart->addItem(
            ProductSnapshot::create($productId, '
            Camiseta', Money::create(1000)),
            Quantity::create(1),
        );
        return $cart;
    }

    public function testEliminaItemCorrectamente(): void
    {
        $cart = $this->makeCartConItem('prod-1');

        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn($cart);
        $repo->expects($this->once())->method('save');

        (new RemoveItemFromCartHandler($repo))(new RemoveItemFromCart('cart-1', 'prod-1'));

        $this->assertEmpty($cart->items());
    }

    public function testLanzaExcepcionSiCarritoNoEncontrado(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Carrito no encontrado');

        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->never())->method('save');

        (new RemoveItemFromCartHandler($repo))(new RemoveItemFromCart('cart-1', 'prod-1'));
    }

    public function testLanzaExcepcionSiProductoNoEstaEnCarrito(): void
    {
        $this->expectException(DomainException::class);

        $cart = Cart::create(CartId::create('cart-1'), 'user-1');

        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn($cart);
        $repo->expects($this->never())->method('save');

        (new RemoveItemFromCartHandler($repo))(new RemoveItemFromCart('cart-1', 'prod-inexistente'));
    }
}
