<?php

declare(strict_types=1);

namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\AddItemToCart;
use App\Cart\Application\Command\AddItemToCartHandler;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class AddItemToCartHandlerTest extends TestCase
{
    private function makeCommand(
        string $cartId = 'cart-1',
        string $userId = 'user-1',
        string $productId = 'prod-1',
        string $productName = 'Camiseta',
        int $productPrice = 1000,
        int $quantity = 2,
    ): AddItemToCart {
        return new AddItemToCart($cartId, $userId, $productId, $productName, $productPrice, $quantity);
    }

    public function testCreaCartNuevoSiNoExiste(): void
    {
        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(function (Cart $cart): bool {
                return $cart->id()->value() === 'cart-1'
                    && count($cart->items()) === 1;
            }));

        (new AddItemToCartHandler($repo))($this->makeCommand());
    }

    public function testAñadeItemACartExistente(): void
    {
        $cart = Cart::create(CartId::create('cart-1'), 'user-1');

        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn($cart);
        $repo->expects($this->once())->method('save');

        (new AddItemToCartHandler($repo))($this->makeCommand());

        $this->assertCount(1, $cart->items());
        $this->assertSame('prod-1', $cart->items()[0]->product()->productId());
    }

    public function testActualizaCantidadSiProductoYaExiste(): void
    {
        $cart = Cart::create(CartId::create('cart-1'), 'user-1');

        $repo = $this->createMock(CartRepositoryInterface::class);
        $repo->method('findById')->willReturn($cart);
        $repo->expects($this->exactly(2))->method('save');

        $handler = new AddItemToCartHandler($repo);
        $handler($this->makeCommand(quantity: 2));
        $handler($this->makeCommand(quantity: 5));

        $this->assertCount(1, $cart->items());
        $this->assertSame(5, $cart->items()[0]->quantity()->value());
    }
}
