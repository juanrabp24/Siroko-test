<?php

declare(strict_types=1);

namespace App\Tests\Order\Domain\Model;

use App\Order\Domain\Model\OrderItem;
use App\Shared\Domain\Money;
use PHPUnit\Framework\TestCase;

final class OrderItemTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $item = OrderItem::create('prod-1', 'Camiseta térmica', Money::create(1250), 2);

        $this->assertSame('prod-1', $item->productId());
        $this->assertSame('Camiseta térmica', $item->productName());
        $this->assertSame(1250, $item->unitPrice()->amount());
        $this->assertSame(2, $item->quantity());
    }

    public function testTotal(): void
    {
        $item = OrderItem::create('prod-1', 'Camiseta térmica', Money::create(1250), 3);

        $this->assertSame(3750, $item->total()->amount());
    }

    public function testTotalUnidadUnica(): void
    {
        $item = OrderItem::create('prod-1', 'Maillot', Money::create(3500), 1);

        $this->assertSame(3500, $item->total()->amount());
    }
}
