<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
class PaymentEntity
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $orderId;

    #[ORM\Column]
    private int $amount;

    #[ORM\Column(length: 50)]
    private string $status;

    public function __construct(string $id, string $orderId, int $amount, string $status)
    {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->status = $status;
    }

    public function getId(): string { return $this->id; }
    public function getOrderId(): string { return $this->orderId; }
    public function getAmount(): int { return $this->amount; }
    public function getStatus(): string { return $this->status; }

    public function update(string $status): void
    {
        $this->status = $status;
    }
}
