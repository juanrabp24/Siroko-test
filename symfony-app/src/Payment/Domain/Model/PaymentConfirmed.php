<?php

declare(strict_types=1);

namespace App\Payment\Domain\Model;

final class PaymentConfirmed
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $orderId,
    ) {}
}
