<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain\Model;

use App\Cart\Domain\Model\Quantity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $quantity = Quantity::create(3);

        $this->assertSame(3, $quantity->value());
    }

    public function testCero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Quantity::create(0);
    }

    public function testNegativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Quantity::create(-1);
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(Quantity::create(3)->equals(Quantity::create(3)));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(Quantity::create(3)->equals(Quantity::create(5)));
    }
}
