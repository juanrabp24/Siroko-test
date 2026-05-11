<?php

declare(strict_types=1);

namespace App\Tests\Order\Domain\Model;

use App\Order\Domain\Model\OrderStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OrderStatusTest extends TestCase
{
    public function testCreacionPending(): void
    {
        $status = OrderStatus::create('pending');

        $this->assertTrue($status->isPending());
        $this->assertSame('pending', $status->value());
    }

    public function testCreacionPaid(): void
    {
        $status = OrderStatus::create('paid');

        $this->assertTrue($status->isPaid());
    }

    public function testCreacionCancelled(): void
    {
        $status = OrderStatus::create('cancelled');

        $this->assertTrue($status->isCancelled());
    }

    public function testEstadoInvalido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        OrderStatus::create('desconocido');
    }

    public function testFactoriaPending(): void
    {
        $this->assertTrue(OrderStatus::pending()->isPending());
    }

    public function testFactoriaPaid(): void
    {
        $this->assertTrue(OrderStatus::paid()->isPaid());
    }

    public function testFactoriaCancelled(): void
    {
        $this->assertTrue(OrderStatus::cancelled()->isCancelled());
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(OrderStatus::pending()->equals(OrderStatus::pending()));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(OrderStatus::pending()->equals(OrderStatus::paid()));
    }
}
