<?php

declare(strict_types=1);

namespace App\Payment\Application\Command;

final class ProcessPayment
{
    public function __construct(
        private readonly string $paymentId,
        private readonly string $orderId,
        private readonly int $amount,
    ) {}

    public function paymentId(): string
    {
        return $this->paymentId;
    }

    public function orderId(): string
    {
        return $this->orderId;
    }

    public function amount(): int
    {
        return $this->amount;
    }
}
