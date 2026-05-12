<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query;

final class GetProductByIdResponse
{
    public function __construct(
        public readonly string $productId,
        public readonly string $name,
        public readonly int $price,
        public readonly int $stock,
        public readonly bool $available,
    ) {}
}
