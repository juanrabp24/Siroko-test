<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Persistence;

use App\Payment\Domain\Model\Payment;
use App\Payment\Domain\Model\PaymentId;
use App\Payment\Domain\Model\PaymentStatus;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Shared\Domain\Money;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrinePaymentRepository extends ServiceEntityRepository implements PaymentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentEntity::class);
    }

    public function findById(PaymentId $id): ?Payment
    {
        $entity = $this->find($id->value());
        return $entity !== null ? $this->toDomain($entity) : null;
    }

    public function findByOrderId(string $orderId): ?Payment
    {
        $entity = $this->createQueryBuilder('p')
            ->where('p.orderId = :orderId')
            ->setParameter('orderId', $orderId)
            ->getQuery()
            ->getOneOrNullResult();

        return $entity !== null ? $this->toDomain($entity) : null;
    }

    public function save(Payment $payment): void
    {
        $em = $this->getEntityManager();
        $entity = $this->find($payment->id()->value());

        if ($entity === null) {
            $entity = new PaymentEntity(
                $payment->id()->value(),
                $payment->orderId(),
                $payment->amount()->amount(),
                $payment->status()->value(),
            );
            $em->persist($entity);
        } else {
            $entity->update($payment->status()->value());
        }

        $em->flush();
    }

    private function toDomain(PaymentEntity $entity): Payment
    {
        return Payment::reconstitute(
            PaymentId::create($entity->getId()),
            $entity->getOrderId(),
            Money::create($entity->getAmount()),
            PaymentStatus::create($entity->getStatus()),
        );
    }
}
