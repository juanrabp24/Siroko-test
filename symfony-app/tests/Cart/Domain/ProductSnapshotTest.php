<?php
namespace App\Tests\Cart\Domain;

use App\Cart\Domain\ProductSnapshot;
use App\Shared\Domain\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductSnapshotTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $snapshot = ProductSnapshot::create('prod-1', 'Camiseta térmica', Money::create(1250));

        $this->assertSame('prod-1', $snapshot->productId());
        $this->assertSame('Camiseta térmica', $snapshot->name());
        $this->assertTrue(Money::create(1250)->equals($snapshot->price()));
    }

    public function testProductIdVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductSnapshot::create('', 'Camiseta térmica', Money::create(1250));
    }

    public function testNombreVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductSnapshot::create('prod-1', '', Money::create(1250));
    }

    public function testIgualdad(): void
    {
        $a = ProductSnapshot::create('prod-1', 'Camiseta térmica', Money::create(1250));
        $b = ProductSnapshot::create('prod-1', 'Camiseta térmica', Money::create(1250));

        $this->assertTrue($a->equals($b));
    }

    public function testDesigualdad(): void
    {
        $a = ProductSnapshot::create('prod-1', 'Camiseta térmica', Money::create(1250));
        $b = ProductSnapshot::create('prod-2', 'Maillot', Money::create(3500));

        $this->assertFalse($a->equals($b));
    }
}
