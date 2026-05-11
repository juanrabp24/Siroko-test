<?php

declare(strict_types=1);

namespace App\Payment\Application\Query;

use App\Payment\Domain\Model\PaymentId;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use DomainException;

final class GetPaymentHandler
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repository,
    ) {}

    public function __invoke(GetPayment $query): GetPaymentResponse
    {
        $payment = $this->repository->findById(PaymentId::create($query->paymentId()));

        if ($payment === null) {
            throw new DomainException('Pago no encontrado');
        }

        return new GetPaymentResponse(
            paymentId: $payment->id()->value(),
            orderId:   $payment->orderId(),
            amount:    $payment->amount()->amount(),
            status:    $payment->status()->value(),
        );
    }
}
