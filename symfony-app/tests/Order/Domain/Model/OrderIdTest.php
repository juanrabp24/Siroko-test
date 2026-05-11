<?php

declare(strict_types=1);

namespace App\Tests\Order\Domain\Model;

use App\Order\Domain\Model\OrderId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OrderIdTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $id = OrderId::create('order-123');

        $this->assertSame('order-123', $id->value());
    }

    public function testVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        OrderId::create('');
    }

    public function testEspacios(): void
    {
        $this->expectException(InvalidArgumentException::class);

        OrderId::create('   ');
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(OrderId::create('order-123')->equals(OrderId::create('order-123')));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(OrderId::create('order-123')->equals(OrderId::create('order-456')));
    }
}
