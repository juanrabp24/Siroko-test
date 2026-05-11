<?php

declare(strict_types=1);

namespace App\Cart\Application\Query;

final class GetCart
{
    public function __construct(
        private readonly string $cartId,
    ) {}

    public function cartId(): string
    {
        return $this->cartId;
    }
}
