<?php

declare(strict_types=1);

namespace App\Cart\Domain\Model;

use App\Shared\Domain\Money;
use DomainException;

final class Cart
{
    private array $items = [];
    private array $domainEvents = [];
    private bool $checked = false;

    private function __construct(
        private readonly CartId $id,
        private readonly string $userId,
    ) {}

    public static function create(CartId $id, string $userId): self
    {
        return new self($id, $userId);
    }

    public static function reconstitute(CartId $id, string $userId, bool $checked, array $items): self
    {
        $cart = new self($id, $userId);
        $cart->checked = $checked;
        $cart->items = $items;
        return $cart;
    }

    public function addItem(ProductSnapshot $product, Quantity $quantity): void
    {
        if ($this->checked) {
            throw new DomainException('No se pueden añadir items a un carrito cerrado');
        }

        foreach ($this->items as $item) {
            if ($item->product()->productId() === $product->productId()) {
                $item->updateQuantity($quantity);
                return;
            }
        }

        $this->items[] = CartItem::create($product, $quantity);
    }

    public function removeItem(string $productId): void
    {
        if ($this->checked) {
            throw new DomainException('No se pueden eliminar items de un carrito cerrado');
        }

        foreach ($this->items as $key => $item) {
            if ($item->product()->productId() === $productId) {
                unset($this->items[$key]);
                return;
            }
        }

        throw new DomainException('El producto no existe en el carrito');
    }

    public function checkout(): void
    {
        if ($this->checked) {
            throw new DomainException('El carrito ya ha sido procesado');
        }

        if (empty($this->items)) {
            throw new DomainException('No se puede hacer checkout de un carrito vacío');
        }

        $this->checked = true;
        $this->domainEvents[] = new CartCheckedOut($this->id->value(), $this->userId);
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

    public function id(): CartId
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function isChecked(): bool
    {
        return $this->checked;
    }
}
