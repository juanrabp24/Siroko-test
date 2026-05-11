<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartCheckedOut;
use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Model\ProductSnapshot;
use App\Cart\Domain\Model\Quantity;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CartTest extends TestCase
{
    private function makeCart(): Cart
    {
        return Cart::create(CartId::create('cart-1'), 'user-1');
    }

    private function makeProduct(string $id = 'prod-1', int $price = 1000): ProductSnapshot
    {
        return ProductSnapshot::create($id, 'Producto test', Money::create($price));
    }

    public function testCreacionValida(): void
    {
        $cart = $this->makeCart();

        $this->assertSame('cart-1', $cart->id()->value());
        $this->assertSame('user-1', $cart->userId());
        $this->assertEmpty($cart->items());
    }

    public function testAddItem(): void
    {
        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(2));

        $this->assertCount(1, $cart->items());
    }

    public function testAddItemMismoProductoActualizaCantidad(): void
    {
        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(2));
        $cart->addItem($this->makeProduct(), Quantity::create(5));

        $this->assertCount(1, $cart->items());
        $this->assertSame(5, $cart->items()[0]->quantity()->value());
    }

    public function testAddItemCarritoCerrado(): void
    {
        $this->expectException(DomainException::class);

        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(1));
        $cart->checkout();
        $cart->addItem($this->makeProduct('prod-2'), Quantity::create(1));
    }

    public function testRemoveItem(): void
    {
        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(1));
        $cart->removeItem('prod-1');

        $this->assertEmpty($cart->items());
    }

    public function testRemoveItemInexistente(): void
    {
        $this->expectException(DomainException::class);

        $cart = $this->makeCart();
        $cart->removeItem('prod-inexistente');
    }

    public function testRemoveItemCarritoCerrado(): void
    {
        $this->expectException(DomainException::class);

        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(1));
        $cart->checkout();
        $cart->removeItem('prod-1');
    }

    public function testCheckout(): void
    {
        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(1));
        $cart->checkout();

        $this->assertTrue($cart->isChecked());
    }

    public function testCheckoutVacio(): void
    {
        $this->expectException(DomainException::class);

        $cart = $this->makeCart();
        $cart->checkout();
    }

    public function testCheckoutDobleVez(): void
    {
        $this->expectException(DomainException::class);

        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(1));
        $cart->checkout();
        $cart->checkout();
    }

    public function testCheckoutLanzaEvento(): void
    {
        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(1));
        $cart->checkout();

        $events = $cart->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(CartCheckedOut::class, $events[0]);
        $this->assertSame('cart-1', $events[0]->cartId);
    }

    public function testPullDomainEventosLosVacia(): void
    {
        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct(), Quantity::create(1));
        $cart->checkout();

        $cart->pullDomainEvents();
        $events = $cart->pullDomainEvents();

        $this->assertEmpty($events);
    }

    public function testTotal(): void
    {
        $cart = $this->makeCart();
        $cart->addItem($this->makeProduct('prod-1', 1000), Quantity::create(2));
        $cart->addItem($this->makeProduct('prod-2', 500), Quantity::create(3));

        $this->assertSame(3500, $cart->total()->amount());
    }
}
