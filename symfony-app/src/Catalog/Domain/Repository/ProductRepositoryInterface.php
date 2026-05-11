<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductId;

interface ProductRepositoryInterface
{
    public function findById(ProductId $id): ?Product;
    public function findAvailable(): array;
    public function save(Product $product): void;
}
