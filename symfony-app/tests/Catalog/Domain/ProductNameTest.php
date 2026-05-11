<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain;

use App\Catalog\Domain\Model\ProductName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductNameTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $name = ProductName::create('Camiseta térmica');

        $this->assertSame('Camiseta térmica', $name->value());
    }

    public function testVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductName::create('');
    }

    public function testEspaciosSolos(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductName::create('   ');
    }

    public function testDemasiadoCorto(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductName::create('A');
    }

    public function testDemasiadoLargo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductName::create(str_repeat('A', 256));
    }

    public function testEspaciosSeRecortan(): void
    {
        $name = ProductName::create('  Maillot  ');

        $this->assertSame('Maillot', $name->value());
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(ProductName::create('Maillot')->equals(ProductName::create('Maillot')));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(ProductName::create('Maillot')->equals(ProductName::create('Camiseta')));
    }
}
