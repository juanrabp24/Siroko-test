<?php

declare(strict_types=1);

namespace App\Payment\Application\Query;

final class GetPayment
{
    public function __construct(
        private readonly string $paymentId,
    ) {}

    public function paymentId(): string
    {
        return $this->paymentId;
    }
}
