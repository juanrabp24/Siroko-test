<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query;

use App\Catalog\Domain\Model\ProductId;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use DomainException;

final class GetProductByIdHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function __invoke(GetProductById $query): GetProductByIdResponse
    {
        $product = $this->repository->findById(ProductId::create($query->productId()));

        if ($product === null) {
            throw new DomainException('Producto no encontrado');
        }

        return new GetProductByIdResponse(
            productId: $product->id()->value(),
            name:      $product->name()->value(),
            price:     $product->price()->amount(),
            stock:     $product->stock()->value(),
            available: $product->isAvailable(),
        );
    }
}
