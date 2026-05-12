<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductId;
use App\Catalog\Domain\Model\ProductName;
use App\Catalog\Domain\Model\Stock;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Shared\Domain\Money;

final class CreateProductHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function __invoke(CreateProduct $command): void
    {
        $product = Product::create(
            ProductId::create($command->productId()),
            ProductName::create($command->name()),
            Money::create($command->price()),
            Stock::create($command->stock()),
        );

        $this->repository->save($product);
    }
}
