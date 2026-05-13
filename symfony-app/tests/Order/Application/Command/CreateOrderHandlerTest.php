<?php

declare(strict_types=1);

namespace App\Tests\Order\Application\Command;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Model\CartItem;
use App\Cart\Domain\Model\ProductSnapshot;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Order\Application\Command\CreateOrder;
use App\Order\Application\Command\CreateOrderHandler;
use App\Order\Domain\Model\Order;
use App\Order\Domain\Repository\OrderRepositoryInterface;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CreateOrderHandlerTest extends TestCase
{
    private function makeCommand(string $cartId = 'cart-1'): CreateOrder
    {
        return new CreateOrder(
            'order-1',
            $cartId,
            'user-1',
            'Calle Mayor 1',
            'Madrid',
            '28001',
            'ES',
            'Juan García',
        );
    }

    private function makeCartChecked(): Cart
    {
        $item = CartItem::create(
            ProductSnapshot::create('prod-1', 'Camiseta', Money::create(150)),
            Quantity::create(2),
        );
        return Cart::reconstitute(CartId::create('cart-1'), 'user-1', true, [$item]);
    }

    public function testCreaOrdenDesdeCarritoChecked(): void
    {
        $cartRepo = $this->createStub(CartRepositoryInterface::class);
        $cartRepo->method('findById')->willReturn($this->makeCartChecked());

        $orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $orderRepo->expects($this->once())->method('save')
            ->with($this->callback(fn(Order $o): bool => $o->id()->value() === 'order-1'));

        (new CreateOrderHandler($cartRepo, $orderRepo))($this->makeCommand());
    }

    public function testOrdenGuardadaContieneLosItemsDelCarrito(): void
    {
        $cartRepo = $this->createStub(CartRepositoryInterface::class);
        $cartRepo->method('findById')->willReturn($this->makeCartChecked());

        $savedOrder = null;
        $orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $orderRepo->expects($this->once())->method('save')
            ->willReturnCallback(function (Order $order) use (&$savedOrder): void {
                $savedOrder = $order;
            });

        (new CreateOrderHandler($cartRepo, $orderRepo))($this->makeCommand());

        $this->assertNotNull($savedOrder);
        $this->assertCount(1, $savedOrder->items());
        $this->assertSame('prod-1', $savedOrder->items()[0]->productId());
        $this->assertSame('Camiseta', $savedOrder->items()[0]->productName());
        $this->assertSame(300, $savedOrder->total()->amount());
    }

    public function testOrdenGuardadaTieneShippingAddressCorrecto(): void
    {
        $cartRepo = $this->createStub(CartRepositoryInterface::class);
        $cartRepo->method('findById')->willReturn($this->makeCartChecked());

        $savedOrder = null;
        $orderRepo = $this->createStub(OrderRepositoryInterface::class);
        $orderRepo->method('save')
            ->willReturnCallback(function (Order $order) use (&$savedOrder): void {
                $savedOrder = $order;
            });

        (new CreateOrderHandler($cartRepo, $orderRepo))($this->makeCommand());

        $addr = $savedOrder->shippingAddress();
        $this->assertSame('Calle Mayor 1', $addr->street());
        $this->assertSame('Madrid', $addr->city());
        $this->assertSame('28001', $addr->postalCode());
        $this->assertSame('ES', $addr->country());
        $this->assertSame('Juan García', $addr->recipientName());
    }

    public function testLanzaExcepcionSiCarritoNoEncontrado(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Carrito no encontrado');

        $cartRepo = $this->createStub(CartRepositoryInterface::class);
        $cartRepo->method('findById')->willReturn(null);

        $orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $orderRepo->expects($this->never())->method('save');

        (new CreateOrderHandler($cartRepo, $orderRepo))($this->makeCommand());
    }

    public function testLanzaExcepcionSiCarritoNoEstaChecked(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El carrito no ha sido procesado');

        $cart = Cart::create(CartId::create('cart-1'), 'user-1');

        $cartRepo = $this->createStub(CartRepositoryInterface::class);
        $cartRepo->method('findById')->willReturn($cart);

        $orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $orderRepo->expects($this->never())->method('save');

        (new CreateOrderHandler($cartRepo, $orderRepo))($this->makeCommand());
    }
}
