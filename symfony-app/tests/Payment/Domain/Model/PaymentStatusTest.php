<?php

declare(strict_types=1);

namespace App\Tests\Payment\Domain\Model;

use App\Payment\Domain\Model\PaymentStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PaymentStatusTest extends TestCase
{
    public function testCreacionPending(): void
    {
        $status = PaymentStatus::create('pending');

        $this->assertTrue($status->isPending());
        $this->assertSame('pending', $status->value());
    }

    public function testCreacionSuccess(): void
    {
        $status = PaymentStatus::create('success');

        $this->assertTrue($status->isSuccess());
    }

    public function testCreacionFailed(): void
    {
        $status = PaymentStatus::create('failed');

        $this->assertTrue($status->isFailed());
    }

    public function testEstadoInvalido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PaymentStatus::create('desconocido');
    }

    public function testFactoriaPending(): void
    {
        $this->assertTrue(PaymentStatus::pending()->isPending());
    }

    public function testFactoriaSuccess(): void
    {
        $this->assertTrue(PaymentStatus::success()->isSuccess());
    }

    public function testFactoriaFailed(): void
    {
        $this->assertTrue(PaymentStatus::failed()->isFailed());
    }

    public function testIgualdad(): void
    {
        $this->assertTrue(PaymentStatus::pending()->equals(PaymentStatus::pending()));
    }

    public function testDesigualdad(): void
    {
        $this->assertFalse(PaymentStatus::pending()->equals(PaymentStatus::success()));
    }
}
