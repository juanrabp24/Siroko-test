<?php

declare(strict_types=1);

namespace App\Payment\Domain\Model;

use App\Shared\Domain\Money;
use DomainException;

final class Payment
{
    private array $domainEvents = [];

    private function __construct(
        private readonly PaymentId $id,
        private readonly string $orderId,
        private readonly Money $amount,
        private PaymentStatus $status,
    ) {}

    public static function create(
        PaymentId $id,
        string $orderId,
        Money $amount,
    ): self {
        return new self($id, $orderId, $amount, PaymentStatus::pending());
    }

    public static function reconstitute(PaymentId $id, string $orderId, Money $amount, PaymentStatus $status): self
    {
        return new self($id, $orderId, $amount, $status);
    }

    public function confirm(): void
    {
        if (!$this->status->isPending()) {
            throw new DomainException('Solo se pueden confirmar pagos pendientes');
        }

        $this->status = PaymentStatus::success();
        $this->domainEvents[] = new PaymentConfirmed($this->id->value(), $this->orderId);
    }

    public function fail(): void
    {
        if (!$this->status->isPending()) {
            throw new DomainException('Solo se pueden fallar pagos pendientes');
        }

        $this->status = PaymentStatus::failed();
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    public function id(): PaymentId
    {
        return $this->id;
    }

    public function orderId(): string
    {
        return $this->orderId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function status(): PaymentStatus
    {
        return $this->status;
    }
}
