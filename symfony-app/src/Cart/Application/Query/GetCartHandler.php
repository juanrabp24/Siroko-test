<?php

declare(strict_types=1);

namespace App\Cart\Application\Query;

use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use DomainException;

final class GetCartHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
    ) {}

    public function __invoke(GetCart $query): GetCartResponse
    {
        $cart = $this->repository->findById(CartId::create($query->cartId()));

        if ($cart === null) {
            throw new DomainException('Carrito no encontrado');
        }

        $items = array_map(fn($item) => [
            'productId'   => $item->product()->productId(),
            'productName' => $item->product()->name(),
            'unitPrice'   => $item->product()->price()->amount(),
            'quantity'    => $item->quantity()->value(),
            'total'       => $item->total()->amount(),
        ], $cart->items());

        return new GetCartResponse(
            cartId: $cart->id()->value(),
            userId: $cart->userId(),
            items: $items,
            total: $cart->total()->amount(),
            isChecked: $cart->isChecked(),
        );
    }
}
