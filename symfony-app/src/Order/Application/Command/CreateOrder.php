<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

final class CreateOrder
{
    public function __construct(
        private readonly string $orderId,
        private readonly string $cartId,
        private readonly string $userId,
        private readonly string $street,
        private readonly string $city,
        private readonly string $postalCode,
        private readonly string $country,
        private readonly string $recipientName,
    ) {}

    public function orderId(): string
    {
        return $this->orderId;
    }

    public function cartId(): string
    {
        return $this->cartId;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function street(): string
    {
        return $this->street;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function postalCode(): string
    {
        return $this->postalCode;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function recipientName(): string
    {
        return $this->recipientName;
    }
}
