<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductId;
use App\Catalog\Domain\Model\ProductName;
use App\Catalog\Domain\Model\Stock;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Shared\Domain\Money;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineProductRepository extends ServiceEntityRepository implements ProductRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductEntity::class);
    }

    public function findById(ProductId $id): ?Product
    {
        $entity = $this->find($id->value());
        return $entity !== null ? $this->toDomain($entity) : null;
    }

    public function findAvailable(): array
    {
        $entities = $this->createQueryBuilder('p')
            ->where('p.stock > 0')
            ->getQuery()
            ->getResult();

        return array_map($this->toDomain(...), $entities);
    }

    public function save(Product $product): void
    {
        $em = $this->getEntityManager();
        $entity = $this->find($product->id()->value());

        if ($entity === null) {
            $entity = new ProductEntity(
                $product->id()->value(),
                $product->name()->value(),
                $product->price()->amount(),
                $product->stock()->value(),
            );
            $em->persist($entity);
        } else {
            $entity->update(
                $product->name()->value(),
                $product->price()->amount(),
                $product->stock()->value(),
            );
        }

        $em->flush();
    }

    private function toDomain(ProductEntity $entity): Product
    {
        return Product::reconstitute(
            ProductId::create($entity->getId()),
            ProductName::create($entity->getName()),
            Money::create($entity->getPrice()),
            Stock::create($entity->getStock()),
        );
    }
}
