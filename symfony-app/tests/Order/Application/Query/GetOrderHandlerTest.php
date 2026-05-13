<?php

declare(strict_types=1);

namespace App\Tests\Order\Application\Query;

use App\Order\Application\Query\GetOrder;
use App\Order\Application\Query\GetOrderHandler;
use App\Order\Application\Query\GetOrderResponse;
use App\Order\Domain\Model\Order;
use App\Order\Domain\Model\OrderId;
use App\Order\Domain\Model\OrderItem;
use App\Order\Domain\Model\ShippingAddress;
use App\Order\Domain\Repository\OrderRepositoryInterface;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class GetOrderHandlerTest extends TestCase
{
    private function makeShippingAddress(): ShippingAddress
    {
        return ShippingAddress::create('Calle Mayor 1', 'Madrid', '28001', 'ES', 'Juan García');
    }

    private function makeOrdenConItem(): Order
    {
        $order = Order::create(
            OrderId::create('order-1'),
            'user-1',
            $this->makeShippingAddress(),
        );
        $order->addItem(OrderItem::create('prod-1', 'Camiseta', Money::create(150), 2));
        return $order;
    }

    public function testDevuelveGetOrderResponse(): void
    {
        $repo = $this->createStub(OrderRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makeOrdenConItem());

        $response = (new GetOrderHandler($repo))(new GetOrder('order-1'));

        $this->assertInstanceOf(GetOrderResponse::class, $response);
        $this->assertSame('order-1', $response->orderId);
        $this->assertSame('user-1', $response->userId);
        $this->assertSame('pending', $response->status);
        $this->assertSame(300, $response->total);
    }

    public function testResponseContieneItemsCorrectos(): void
    {
        $repo = $this->createStub(OrderRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makeOrdenConItem());

        $response = (new GetOrderHandler($repo))(new GetOrder('order-1'));

        $this->assertCount(1, $response->items);
        $item = $response->items[0];
        $this->assertSame('prod-1', $item['productId']);
        $this->assertSame('Camiseta', $item['productName']);
        $this->assertSame(150, $item['unitPrice']);
        $this->assertSame(2, $item['quantity']);
        $this->assertSame(300, $item['total']);
    }

    public function testResponseContieneShippingAddressCorrecto(): void
    {
        $repo = $this->createStub(OrderRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makeOrdenConItem());

        $response = (new GetOrderHandler($repo))(new GetOrder('order-1'));

        $this->assertSame('Calle Mayor 1', $response->street);
        $this->assertSame('Madrid', $response->city);
        $this->assertSame('28001', $response->postalCode);
        $this->assertSame('ES', $response->country);
        $this->assertSame('Juan García', $response->recipientName);
    }

    public function testLanzaExcepcionSiOrdenNoEncontrada(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Orden no encontrada');

        $repo = $this->createStub(OrderRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        (new GetOrderHandler($repo))(new GetOrder('order-inexistente'));
    }
}
