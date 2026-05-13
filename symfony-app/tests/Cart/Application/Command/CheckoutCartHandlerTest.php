<?php

declare(strict_types=1);

namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\CheckoutCart;
use App\Cart\Application\Command\CheckoutCartHandler;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Model\ProductSnapshot;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CheckoutCartHandlerTest extends TestCase
{
    private function makeCartConItem(): Cart
    {
        $cart = Cart::create(CartId::create('cart-1'), 'user-1');
        $cart->addItem(
            ProductSnapshot::create('prod-1', 'Camiseta', Money::create(1000)),
            Quantity::create(1),
        );
        return $cart;
    }

    public function testHaceCheckoutCorrectamente(): void
    {
        $cart = $this->makeCartConItem();

        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn($cart);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn(Cart $c): bool => $c->isChecked()));

        (new CheckoutCartHandler($repo))(new CheckoutCart('cart-1'));
    }

    public function testLanzaExcepcionSiCarritoNoEncontrado(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Carrito no encontrado');

        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->never())->method('save');

        (new CheckoutCartHandler($repo))(new CheckoutCart('cart-1'));
    }

    public function testLanzaExcepcionSiCarritoEstaVacio(): void
    {
        $this->expectException(DomainException::class);

        $cart = Cart::create(CartId::create('cart-1'), 'user-1');

        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn($cart);
        $repo->expects($this->never())->method('save');

        (new CheckoutCartHandler($repo))(new CheckoutCart('cart-1'));
    }
}
