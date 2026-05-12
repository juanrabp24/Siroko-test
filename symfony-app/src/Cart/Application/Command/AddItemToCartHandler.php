<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Model\ProductSnapshot;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Domain\Money;

final class AddItemToCartHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
    ) {}

    public function __invoke(AddItemToCart $command): void
    {
        $cartId = CartId::create($command->cartId());

        $cart = $this->repository->findById($cartId)
            ?? Cart::create($cartId, $command->userId());

        $cart->addItem(
            ProductSnapshot::create(
                $command->productId(),
                $command->productName(),
                Money::create($command->productPrice()),
            ),
            Quantity::create($command->quantity()),
        );

        $this->repository->save($cart);
    }
}
