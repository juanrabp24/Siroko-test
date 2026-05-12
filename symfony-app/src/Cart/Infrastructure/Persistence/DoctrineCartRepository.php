<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Model\CartItem;
use App\Cart\Domain\Model\ProductSnapshot;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Domain\Money;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineCartRepository extends ServiceEntityRepository implements CartRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartEntity::class);
    }

    public function findById(CartId $id): ?Cart
    {
        $entity = $this->find($id->value());
        return $entity !== null ? $this->toDomain($entity) : null;
    }

    public function findActiveByUserId(string $userId): ?Cart
    {
        $entity = $this->createQueryBuilder('c')
            ->where('c.userId = :userId')
            ->andWhere('c.checked = :checked')
            ->setParameter('userId', $userId)
            ->setParameter('checked', false)
            ->getQuery()
            ->getOneOrNullResult();

        return $entity !== null ? $this->toDomain($entity) : null;
    }

    public function save(Cart $cart): void
    {
        $em = $this->getEntityManager();
        $entity = $this->find($cart->id()->value());

        if ($entity === null) {
            $entity = new CartEntity(
                $cart->id()->value(),
                $cart->userId(),
                $cart->isChecked(),
                $this->serializeItems($cart->items()),
            );
            $em->persist($entity);
        } else {
            $entity->update($cart->isChecked(), $this->serializeItems($cart->items()));
        }

        $em->flush();
    }

    private function toDomain(CartEntity $entity): Cart
    {
        $items = array_map(
            fn(array $data) => CartItem::create(
                ProductSnapshot::create($data['productId'], $data['name'], Money::create($data['price'])),
                Quantity::create($data['quantity']),
            ),
            $entity->getItems(),
        );

        return Cart::reconstitute(
            CartId::create($entity->getId()),
            $entity->getUserId(),
            $entity->isChecked(),
            $items,
        );
    }

    private function serializeItems(array $items): array
    {
        return array_map(
            fn(CartItem $item) => [
                'productId' => $item->product()->productId(),
                'name'      => $item->product()->name(),
                'price'     => $item->product()->price()->amount(),
                'quantity'  => $item->quantity()->value(),
            ],
            $items,
        );
    }
}
