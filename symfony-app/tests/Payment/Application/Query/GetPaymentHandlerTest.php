<?php

declare(strict_types=1);

namespace App\Tests\Payment\Application\Query;

use App\Payment\Application\Query\GetPayment;
use App\Payment\Application\Query\GetPaymentHandler;
use App\Payment\Application\Query\GetPaymentResponse;
use App\Payment\Domain\Model\Payment;
use App\Payment\Domain\Model\PaymentId;
use App\Payment\Domain\Model\PaymentStatus;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Shared\Domain\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class GetPaymentHandlerTest extends TestCase
{
    private function makePago(string $status = 'success'): Payment
    {
        return Payment::reconstitute(
            PaymentId::create('pay-1'),
            'order-1',
            Money::create(5000),
            PaymentStatus::create($status),
        );
    }

    public function testDevuelveGetPaymentResponse(): void
    {
        $repo = $this->createStub(PaymentRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makePago('success'));

        $response = (new GetPaymentHandler($repo))(new GetPayment('pay-1'));

        $this->assertInstanceOf(GetPaymentResponse::class, $response);
        $this->assertSame('pay-1', $response->paymentId);
        $this->assertSame('order-1', $response->orderId);
        $this->assertSame(5000, $response->amount);
        $this->assertSame('success', $response->status);
    }

    public function testDevuelveResponseConEstadoPending(): void
    {
        $repo = $this->createStub(PaymentRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->makePago('pending'));

        $response = (new GetPaymentHandler($repo))(new GetPayment('pay-1'));

        $this->assertSame('pending', $response->status);
    }

    public function testLanzaExcepcionSiPagoNoEncontrado(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Pago no encontrado');

        $repo = $this->createStub(PaymentRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        (new GetPaymentHandler($repo))(new GetPayment('pay-inexistente'));
    }
}
