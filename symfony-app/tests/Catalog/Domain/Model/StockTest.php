<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain\Model;

use App\Catalog\Domain\Model\Stock;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class StockTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $stock = Stock::create(10);

        $this->assertSame(10, $stock->value());
    }

    public function testCeroEsValido(): void
    {
        $stock = Stock::create(0);

        $this->assertSame(0, $stock->value());
    }

    public function testNegativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Stock::create(-1);
    }

    public function testDisponibleConStock(): void
    {
        $this->assertTrue(Stock::create(5)->isAvailable());
    }

    public function testNoDisponibleSinStock(): void
    {
        $this->assertFalse(Stock::create(0)->isAvailable());
    }

    public function testDecrease(): void
    {
        $stock = Stock::create(10)->decrease(3);

        $this->assertSame(7, $stock->value());
    }

    public function testDecreaseHastaAgotar(): void
    {
        $stock = Stock::create(5)->decrease(5);

        $this->assertSame(0, $stock->value());
    }

    public function testDecreaseInsuficiente(): void
    {
        $this->expectException(DomainException::class);

        Stock::create(3)->decrease(5);
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(Stock::create(10)->equals(Stock::create(10)));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(Stock::create(10)->equals(Stock::create(5)));
    }
}
