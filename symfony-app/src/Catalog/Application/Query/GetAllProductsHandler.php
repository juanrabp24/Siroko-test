<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query;

use App\Catalog\Domain\Repository\ProductRepositoryInterface;

final class GetAllProductsHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    /** @return GetProductByIdResponse[] */
    public function __invoke(GetAllProducts $query): array
    {
        return array_map(
            fn ($product) => new GetProductByIdResponse(
                productId: $product->id()->value(),
                name:      $product->name()->value(),
                price:     $product->price()->amount(),
                stock:     $product->stock()->value(),
                available: $product->isAvailable(),
            ),
            $this->repository->findAvailable(),
        );
    }
}
