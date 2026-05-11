<?php

declare(strict_types=1);

namespace App\Order\Domain\Model;

use App\Shared\Domain\Money;
use DomainException;

final class Order
{
    private array $items = [];
    private array $domainEvents = [];

    private function __construct(
        private readonly OrderId $id,
        private readonly string $userId,
        private readonly ShippingAddress $shippingAddress,
        private OrderStatus $status,
    ) {}

    public static function create(
        OrderId $id,
        string $userId,
        ShippingAddress $shippingAddress,
    ): self {
        $order = new self($id, $userId, $shippingAddress, OrderStatus::pending());
        $order->domainEvents[] = new OrderCreated($id->value(), $userId);
        return $order;
    }

    public static function reconstitute(
        OrderId $id,
        string $userId,
        OrderStatus $status,
        ShippingAddress $shippingAddress,
        array $items,
    ): self {
        $order = new self($id, $userId, $shippingAddress, $status);
        $order->items = $items;
        return $order;
    }

    public function addItem(OrderItem $item): void
    {
        if (!$this->status->isPending()) {
            throw new DomainException('No se pueden añadir items a una orden que no está pendiente');
        }

        $this->items[] = $item;
    }

    public function pay(): void
    {
        if (!$this->status->isPending()) {
            throw new DomainException('Solo se pueden pagar órdenes pendientes');
        }

        $this->status = OrderStatus::paid();
    }

    public function cancel(): void
    {
        if ($this->status->isPaid()) {
            throw new DomainException('No se puede cancelar una orden ya pagada');
        }

        if ($this->status->isCancelled()) {
            throw new DomainException('La orden ya está cancelada');
        }

        $this->status = OrderStatus::cancelled();
    }

    public function total(): Money
    {
        $total = Money::create(0);

        foreach ($this->items as $item) {
            $total = $total->add($item->total());
        }

        return $total;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    public function id(): OrderId
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function shippingAddress(): ShippingAddress
    {
        return $this->shippingAddress;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function items(): array
    {
        return $this->items;
    }
}
