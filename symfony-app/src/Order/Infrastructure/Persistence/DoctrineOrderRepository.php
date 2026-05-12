<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Persistence;

use App\Order\Domain\Model\Order;
use App\Order\Domain\Model\OrderId;
use App\Order\Domain\Model\OrderItem;
use App\Order\Domain\Model\OrderStatus;
use App\Order\Domain\Model\ShippingAddress;
use App\Order\Domain\Repository\OrderRepositoryInterface;
use App\Shared\Domain\Money;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineOrderRepository extends ServiceEntityRepository implements OrderRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderEntity::class);
    }

    public function findById(OrderId $id): ?Order
    {
        $entity = $this->find($id->value());
        return $entity !== null ? $this->toDomain($entity) : null;
    }

    public function save(Order $order): void
    {
        $em = $this->getEntityManager();
        $entity = $this->find($order->id()->value());
        $address = $order->shippingAddress();

        if ($entity === null) {
            $entity = new OrderEntity(
                $order->id()->value(),
                $order->userId(),
                $order->status()->value(),
                $this->serializeItems($order->items()),
                $address->street(),
                $address->city(),
                $address->postalCode(),
                $address->country(),
                $address->recipientName(),
            );
            $em->persist($entity);
        } else {
            $entity->update($order->status()->value(), $this->serializeItems($order->items()));
        }

        $em->flush();
    }

    private function toDomain(OrderEntity $entity): Order
    {
        $items = array_map(
            fn(array $data) => OrderItem::create(
                $data['productId'],
                $data['productName'],
                Money::create($data['unitPrice']),
                $data['quantity'],
            ),
            $entity->getItems(),
        );

        return Order::reconstitute(
            OrderId::create($entity->getId()),
            $entity->getUserId(),
            OrderStatus::create($entity->getStatus()),
            ShippingAddress::create(
                $entity->getStreet(),
                $entity->getCity(),
                $entity->getPostalCode(),
                $entity->getCountry(),
                $entity->getRecipientName(),
            ),
            $items,
        );
    }

    private function serializeItems(array $items): array
    {
        return array_map(
            fn(OrderItem $item) => [
                'productId'   => $item->productId(),
                'productName' => $item->productName(),
                'unitPrice'   => $item->unitPrice()->amount(),
                'quantity'    => $item->quantity(),
            ],
            $items,
        );
    }

    public function findByUserId(string $userId): array
    {
        $entities = $this->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return array_map(fn($entity) => $this->toDomain($entity), $entities);
    }
}
