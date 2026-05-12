<?php

declare(strict_types=1);

namespace App\Order\Domain\Model;

use InvalidArgumentException;

final class ShippingAddress
{
    private function __construct(
        private readonly string $street,
        private readonly string $city,
        private readonly string $postalCode,
        private readonly string $country,
        private readonly string $recipientName,
    ) {}

    public static function create(
        string $street,
        string $city,
        string $postalCode,
        string $country,
        string $recipientName,
    ): self {
        if (empty(trim($street))) {
            throw new InvalidArgumentException('La calle no puede estar vacía');
        }

        if (empty(trim($city))) {
            throw new InvalidArgumentException('La ciudad no puede estar vacía');
        }

        if (empty(trim($postalCode))) {
            throw new InvalidArgumentException('El código postal no puede estar vacío');
        }

        if (empty(trim($country))) {
            throw new InvalidArgumentException('El país no puede estar vacío');
        }

        if (empty(trim($recipientName))) {
            throw new InvalidArgumentException('El nombre del destinatario no puede estar vacío');
        }

        return new self(
            trim($street),
            trim($city),
            trim($postalCode),
            trim($country),
            trim($recipientName),
        );
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

    public function equals(self $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->postalCode === $other->postalCode
            && $this->country === $other->country
            && $this->recipientName === $other->recipientName;
    }
}
