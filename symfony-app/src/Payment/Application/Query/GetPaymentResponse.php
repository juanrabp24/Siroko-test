<?php

declare(strict_types=1);

namespace App\Payment\Application\Query;

final class GetPaymentResponse
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $orderId,
        public readonly int $amount,
        public readonly string $status,
    ) {}
}
