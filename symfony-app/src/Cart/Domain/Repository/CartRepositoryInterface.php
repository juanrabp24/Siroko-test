<?php

declare(strict_types=1);

namespace App\Cart\Domain\Repository;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartId;

interface CartRepositoryInterface
{
    public function findById(CartId $id): ?Cart;
    public function findActiveByUserId(string $userId): ?Cart;
    public function save(Cart $cart): void;
}
