<?php

declare(strict_types=1);

namespace App\Tests\Payment\Domain\Model;

use App\Payment\Domain\Model\PaymentId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PaymentIdTest extends TestCase
{
    public function testCreacionValida(): void
    {
        $id = PaymentId::create('pay-123');

        $this->assertSame('pay-123', $id->value());
    }

    public function testVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PaymentId::create('');
    }

    public function testEspacios(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PaymentId::create('   ');
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(PaymentId::create('pay-123')->equals(PaymentId::create('pay-123')));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(PaymentId::create('pay-123')->equals(PaymentId::create('pay-456')));
    }
}
