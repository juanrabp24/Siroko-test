<?php

declare(strict_types=1);

namespace App\Order\Application\Query;

final class GetOrderResponse
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $userId,
        public readonly string $status,
        public readonly array $items,
        public readonly int $total,
        public readonly string $street,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $country,
        public readonly string $recipientName,
    ) {}
}
