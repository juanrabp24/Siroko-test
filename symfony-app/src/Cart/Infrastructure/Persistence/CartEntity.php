<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'carts')]
class CartEntity
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $userId;

    #[ORM\Column]
    private bool $checked;

    #[ORM\Column(type: Types::JSON)]
    private array $items;

    public function __construct(string $id, string $userId, bool $checked, array $items)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->checked = $checked;
        $this->items = $items;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function isChecked(): bool
    {
        return $this->checked;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function update(bool $checked, array $items): void
    {
        $this->checked = $checked;
        $this->items = $items;
    }
}
