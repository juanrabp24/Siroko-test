<?php

declare(strict_types=1);

namespace App\Cart\Application\Query;

final class GetCartResponse
{
    public function __construct(
        public readonly string $cartId,
        public readonly string $userId,
        public readonly array $items,
        public readonly int $total,
        public readonly bool $isChecked,
    ) {}
}
