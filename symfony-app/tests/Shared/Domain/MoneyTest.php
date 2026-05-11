<?php

declare(strict_types=1);

namespace App\Tests\Shared\Domain;

use App\Shared\Domain\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testImporteValido(): void
    {
        $money = Money::create(1250);

        $this->assertSame(1250, $money->amount());
        $this->assertSame('EUR', $money->currency());
    }

    public function testImporteNegativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::create(-1);
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(Money::create(1000)->equals(Money::create(1000)));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(Money::create(1000)->equals(Money::create(2000)));
    }

    public function testSuma(): void
    {
        $result = Money::create(1000)->add(Money::create(250));

        $this->assertSame(1250, $result->amount());
    }

    public function testMultiplicacion(): void
    {
        $this->assertSame(1500, Money::create(500)->multiply(3)->amount());
    }

    public function testFactorNegativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::create(500)->multiply(-1);
    }

    public function testInmutabilidadSuma(): void
    {
        $original = Money::create(1000);
        $original->add(Money::create(500));

        $this->assertSame(1000, $original->amount());
    }

    public function testInmutabilidadMultiplicacion(): void
    {
        $original = Money::create(1000);
        $original->multiply(3);

        $this->assertSame(1000, $original->amount());
    }
}
