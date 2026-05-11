<?php

declare(strict_types=1);

namespace App\Order\Domain\Model;

final class OrderCreated
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $userId,
    ) {}
}
