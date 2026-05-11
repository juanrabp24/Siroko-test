<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain\Model;

use App\Cart\Domain\Model\CartId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CartIdTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $id = CartId::create('abc-123');

        $this->assertSame('abc-123', $id->value());
    }

    public function testVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CartId::create('');
    }

    public function testEspacios(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CartId::create('   ');
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(CartId::create('abc-123')->equals(CartId::create('abc-123')));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(CartId::create('abc-123')->equals(CartId::create('xyz-456')));
    }
}
