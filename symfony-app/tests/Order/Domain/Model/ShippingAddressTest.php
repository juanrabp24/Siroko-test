<?php

declare(strict_types=1);

namespace App\Tests\Order\Domain\Model;

use App\Order\Domain\Model\ShippingAddress;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ShippingAddressTest extends TestCase
{
    private function makeAddress(
        string $street = 'Calle Mayor 1',
        string $city = 'Madrid',
        string $postalCode = '28001',
        string $country = 'ES',
        string $recipientName = 'Juan García',
    ): ShippingAddress {
        return ShippingAddress::create($street, $city, $postalCode, $country, $recipientName);
    }

    public function testCreacionValida(): void
    {
        $address = $this->makeAddress();

        $this->assertSame('Calle Mayor 1', $address->street());
        $this->assertSame('Madrid', $address->city());
        $this->assertSame('28001', $address->postalCode());
        $this->assertSame('ES', $address->country());
        $this->assertSame('Juan García', $address->recipientName());
    }

    public function testCalleVacia(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeAddress(street: '');
    }

    public function testCiudadVacia(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeAddress(city: '');
    }

    public function testCodigoPostalVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeAddress(postalCode: '');
    }

    public function testPaisVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeAddress(country: '');
    }

    public function testDestinatarioVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeAddress(recipientName: '');
    }

    public function testEspaciosSeRecortan(): void
    {
        $address = $this->makeAddress(street: '  Calle Mayor 1  ', city: '  Madrid  ');

        $this->assertSame('Calle Mayor 1', $address->street());
        $this->assertSame('Madrid', $address->city());
    }

    public function testIgualdad(): void
    {
        $this->assertTrue($this->makeAddress()->equals($this->makeAddress()));
    }

    public function testDesigualdad(): void
    {
        $a = $this->makeAddress(city: 'Madrid');
        $b = $this->makeAddress(city: 'Barcelona');

        $this->assertFalse($a->equals($b));
    }
}
