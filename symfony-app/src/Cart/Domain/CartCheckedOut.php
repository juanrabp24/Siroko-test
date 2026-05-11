<?php

declare(strict_types=1);

namespace App\Cart\Domain;

final class CartCheckedOut
{
    public function __construct(
        public readonly string $cartId,
        public readonly string $userId,
    ) {}
}
