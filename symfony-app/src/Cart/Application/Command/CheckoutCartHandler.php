<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use DomainException;

final class CheckoutCartHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
    ) {}

    public function __invoke(CheckoutCart $command): void
    {
        $cart = $this->repository->findById(CartId::create($command->cartId()));

        if ($cart === null) {
            throw new DomainException('Carrito no encontrado');
        }

        $cart->checkout();

        $this->repository->save($cart);
    }
}
