<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class ProductEntity
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private int $price;

    #[ORM\Column]
    private int $stock;

    public function __construct(string $id, string $name, int $price, int $stock)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function update(string $name, int $price, int $stock): void
    {
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
    }
}
