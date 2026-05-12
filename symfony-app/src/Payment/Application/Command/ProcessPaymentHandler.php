<?php

declare(strict_types=1);

namespace App\Payment\Application\Command;

use App\Payment\Domain\Model\Payment;
use App\Payment\Domain\Model\PaymentId;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Shared\Domain\Money;

final class ProcessPaymentHandler
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repository,
    ) {}

    public function __invoke(ProcessPayment $command): void
    {
        $payment = Payment::create(
            PaymentId::create($command->paymentId()),
            $command->orderId(),
            Money::create($command->amount()),
        );

        $payment->confirm();

        $this->repository->save($payment);
    }
}
