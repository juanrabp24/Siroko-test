<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query;

final class GetProductById
{
    public function __construct(
        private readonly string $productId,
    ) {}

    public function productId(): string
    {
        return $this->productId;
    }
}
