<?php

declare(strict_types=1);

namespace App\Order\Application\Query;

final class GetOrder
{
    public function __construct(
        private readonly string $orderId,
    ) {}

    public function orderId(): string
    {
        return $this->orderId;
    }
}
