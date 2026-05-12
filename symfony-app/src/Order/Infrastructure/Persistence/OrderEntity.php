<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Persistence;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class OrderEntity
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $userId;

    #[ORM\Column(length: 50)]
    private string $status;

    #[ORM\Column(type: Types::JSON)]
    private array $items;

    #[ORM\Column(length: 255)]
    private string $street;

    #[ORM\Column(length: 100)]
    private string $city;

    #[ORM\Column(length: 20)]
    private string $postalCode;

    #[ORM\Column(length: 10)]
    private string $country;

    #[ORM\Column(length: 255)]
    private string $recipientName;

    public function __construct(
        string $id,
        string $userId,
        string $status,
        array $items,
        string $street,
        string $city,
        string $postalCode,
        string $country,
        string $recipientName,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->status = $status;
        $this->items = $items;
        $this->street = $street;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->recipientName = $recipientName;
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getStatus(): string { return $this->status; }
    public function getItems(): array { return $this->items; }
    public function getStreet(): string { return $this->street; }
    public function getCity(): string { return $this->city; }
    public function getPostalCode(): string { return $this->postalCode; }
    public function getCountry(): string { return $this->country; }
    public function getRecipientName(): string { return $this->recipientName; }

    public function update(string $status, array $items): void
    {
        $this->status = $status;
        $this->items = $items;
    }
}
