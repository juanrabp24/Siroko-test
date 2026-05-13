<?php

declare(strict_types=1);

namespace App\Tests\Payment\Application\Command;

use App\Payment\Application\Command\ProcessPayment;
use App\Payment\Application\Command\ProcessPaymentHandler;
use App\Payment\Domain\Model\Payment;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ProcessPaymentHandlerTest extends TestCase
{
    private function makeCommand(
        string $paymentId = 'pay-1',
        string $orderId = 'order-1',
        int $amount = 5000,
    ): ProcessPayment {
        return new ProcessPayment($paymentId, $orderId, $amount);
    }

    public function testProcesaPagoYLoGuarda(): void
    {
        $repo = $this->createMock(PaymentRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(function (Payment $payment): bool {
                return $payment->id()->value() === 'pay-1'
                    && $payment->orderId() === 'order-1'
                    && $payment->amount()->amount() === 5000
                    && $payment->status()->isSuccess();
            }));

        (new ProcessPaymentHandler($repo))($this->makeCommand());
    }

    public function testPagoSeConfirmaAntesDeGuardar(): void
    {
        $savedPayment = null;

        $repo = $this->createMock(PaymentRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->willReturnCallback(function (Payment $payment) use (&$savedPayment): void {
                $savedPayment = $payment;
            });

        (new ProcessPaymentHandler($repo))($this->makeCommand());

        $this->assertNotNull($savedPayment);
        $this->assertTrue($savedPayment->status()->isSuccess());
        $this->assertFalse($savedPayment->status()->isPending());
    }
}
