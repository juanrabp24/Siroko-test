<?php

declare(strict_types=1);

namespace App\Tests\Payment\Domain\Model;

use App\Payment\Domain\Model\Payment;
use App\Payment\Domain\Model\PaymentConfirmed;
use App\Payment\Domain\Model\PaymentId;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class PaymentTest extends TestCase
{
    private function makePayment(string $paymentId = 'pay-1', string $orderId = 'order-1', int $amount = 5000): Payment
    {
        return Payment::create(
            PaymentId::create($paymentId),
            $orderId,
            Money::create($amount),
        );
    }

    public function testCreacionValida(): void
    {
        $payment = $this->makePayment();

        $this->assertSame('pay-1', $payment->id()->value());
        $this->assertSame('order-1', $payment->orderId());
        $this->assertSame(5000, $payment->amount()->amount());
        $this->assertTrue($payment->status()->isPending());
    }

    public function testConfirm(): void
    {
        $payment = $this->makePayment();
        $payment->confirm();

        $this->assertTrue($payment->status()->isSuccess());
    }

    public function testConfirmLanzaEvento(): void
    {
        $payment = $this->makePayment();
        $payment->confirm();

        $events = $payment->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(PaymentConfirmed::class, $events[0]);
        $this->assertSame('pay-1', $events[0]->paymentId);
        $this->assertSame('order-1', $events[0]->orderId);
    }

    public function testPullDomainEventsLosVacia(): void
    {
        $payment = $this->makePayment();
        $payment->confirm();

        $payment->pullDomainEvents();
        $events = $payment->pullDomainEvents();

        $this->assertEmpty($events);
    }

    public function testConfirmPagoYaConfirmado(): void
    {
        $this->expectException(DomainException::class);

        $payment = $this->makePayment();
        $payment->confirm();
        $payment->confirm();
    }

    public function testConfirmPagoFallido(): void
    {
        $this->expectException(DomainException::class);

        $payment = $this->makePayment();
        $payment->fail();
        $payment->confirm();
    }

    public function testFail(): void
    {
        $payment = $this->makePayment();
        $payment->fail();

        $this->assertTrue($payment->status()->isFailed());
    }

    public function testFailPagoYaConfirmado(): void
    {
        $this->expectException(DomainException::class);

        $payment = $this->makePayment();
        $payment->confirm();
        $payment->fail();
    }

    public function testFailPagoYaFallido(): void
    {
        $this->expectException(DomainException::class);

        $payment = $this->makePayment();
        $payment->fail();
        $payment->fail();
    }

    public function testFailNoEmiteEventos(): void
    {
        $payment = $this->makePayment();
        $payment->fail();

        $this->assertEmpty($payment->pullDomainEvents());
    }
}
