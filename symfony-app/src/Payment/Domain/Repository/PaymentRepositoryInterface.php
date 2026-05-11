<?php

declare(strict_types=1);

namespace App\Payment\Domain\Repository;

use App\Payment\Domain\Model\Payment;
use App\Payment\Domain\Model\PaymentId;

interface PaymentRepositoryInterface
{
    public function findById(PaymentId $id): ?Payment;
    public function findByOrderId(string $orderId): ?Payment;
    public function save(Payment $payment): void;
}
