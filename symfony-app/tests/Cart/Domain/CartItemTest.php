<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain;

use App\Cart\Domain\Model\CartItem;
use App\Cart\Domain\Model\ProductSnapshot;
use App\Cart\Domain\Model\Quantity;
use App\Shared\Domain\Money;
use PHPUnit\Framework\TestCase;

final class CartItemTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $item = CartItem::create(
            ProductSnapshot::create('prod-1', 'Camiseta térmica', Money::create(1250)),
            Quantity::create(2)
        );

        $this->assertSame('prod-1', $item->product()->productId());
        $this->assertSame(2, $item->quantity()->value());
    }

    public function testTotal(): void
    {
        $item = CartItem::create(
            ProductSnapshot::create('prod-1', 'Camiseta térmica', Money::create(1250)),
            Quantity::create(3)
        );

        $this->assertSame(3750, $item->total()->amount());
    }

    public function testActualizarCantidad(): void
    {
        $item = CartItem::create(
            ProductSnapshot::create('prod-1', 'Camiseta térmica', Money::create(1250)),
            Quantity::create(2)
        );

        $item->updateQuantity(Quantity::create(5));

        $this->assertSame(5, $item->quantity()->value());
    }
}
