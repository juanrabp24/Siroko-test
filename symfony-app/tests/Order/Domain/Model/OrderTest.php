<?php

declare(strict_types=1);

namespace App\Tests\Order\Domain\Model;

use App\Order\Domain\Model\Order;
use App\Order\Domain\Model\OrderCreated;
use App\Order\Domain\Model\OrderId;
use App\Order\Domain\Model\OrderItem;
use App\Order\Domain\Model\ShippingAddress;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    private function makeOrder(): Order
    {
        return Order::create(
            OrderId::create('order-1'),
            'user-1',
            ShippingAddress::create('Calle Mayor 1', 'Madrid', '28001', 'ES', 'Juan García'),
        );
    }

    private function makeItem(string $productId = 'prod-1', int $price = 1000, int $quantity = 1): OrderItem
    {
        return OrderItem::create($productId, 'Producto test', Money::create($price), $quantity);
    }

    public function testCreacionValida(): void
    {
        $order = $this->makeOrder();

        $this->assertSame('order-1', $order->id()->value());
        $this->assertSame('user-1', $order->userId());
        $this->assertTrue($order->status()->isPending());
        $this->assertEmpty($order->items());
    }

    public function testCreacionLanzaEvento(): void
    {
        $order = $this->makeOrder();

        $events = $order->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderCreated::class, $events[0]);
        $this->assertSame('order-1', $events[0]->orderId);
        $this->assertSame('user-1', $events[0]->userId);
    }

    public function testPullDomainEventsLosVacia(): void
    {
        $order = $this->makeOrder();

        $order->pullDomainEvents();
        $events = $order->pullDomainEvents();

        $this->assertEmpty($events);
    }

    public function testAddItem(): void
    {
        $order = $this->makeOrder();
        $order->pullDomainEvents();
        $order->addItem($this->makeItem());

        $this->assertCount(1, $order->items());
    }

    public function testAddItemOrdenPagada(): void
    {
        $this->expectException(DomainException::class);

        $order = $this->makeOrder();
        $order->addItem($this->makeItem());
        $order->pay();
        $order->addItem($this->makeItem('prod-2'));
    }

    public function testAddItemOrdenCancelada(): void
    {
        $this->expectException(DomainException::class);

        $order = $this->makeOrder();
        $order->cancel();
        $order->addItem($this->makeItem());
    }

    public function testPay(): void
    {
        $order = $this->makeOrder();
        $order->pay();

        $this->assertTrue($order->status()->isPaid());
    }

    public function testPayOrdenYaPagada(): void
    {
        $this->expectException(DomainException::class);

        $order = $this->makeOrder();
        $order->pay();
        $order->pay();
    }

    public function testPayOrdenCancelada(): void
    {
        $this->expectException(DomainException::class);

        $order = $this->makeOrder();
        $order->cancel();
        $order->pay();
    }

    public function testCancel(): void
    {
        $order = $this->makeOrder();
        $order->cancel();

        $this->assertTrue($order->status()->isCancelled());
    }

    public function testCancelOrdenPagada(): void
    {
        $this->expectException(DomainException::class);

        $order = $this->makeOrder();
        $order->pay();
        $order->cancel();
    }

    public function testCancelOrdenYaCancelada(): void
    {
        $this->expectException(DomainException::class);

        $order = $this->makeOrder();
        $order->cancel();
        $order->cancel();
    }

    public function testTotal(): void
    {
        $order = $this->makeOrder();
        $order->addItem($this->makeItem('prod-1', 1000, 2));
        $order->addItem($this->makeItem('prod-2', 500, 3));

        $this->assertSame(3500, $order->total()->amount());
    }

    public function testTotalSinItems(): void
    {
        $order = $this->makeOrder();

        $this->assertSame(0, $order->total()->amount());
    }
}
