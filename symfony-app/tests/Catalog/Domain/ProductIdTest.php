<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain;

use App\Catalog\Domain\Model\ProductId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductIdTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $id = ProductId::create('prod-123');

        $this->assertSame('prod-123', $id->value());
    }

    public function testVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductId::create('');
    }

    public function testEspacios(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductId::create('   ');
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(ProductId::create('prod-123')->equals(ProductId::create('prod-123')));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(ProductId::create('prod-123')->equals(ProductId::create('prod-456')));
    }
}
