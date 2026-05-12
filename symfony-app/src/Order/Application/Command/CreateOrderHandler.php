<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

use App\Cart\Domain\Model\CartId;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Order\Domain\Model\Order;
use App\Order\Domain\Model\OrderId;
use App\Order\Domain\Model\OrderItem;
use App\Order\Domain\Model\ShippingAddress;
use App\Order\Domain\Repository\OrderRepositoryInterface;
use App\Shared\Domain\Money;
use DomainException;

final class CreateOrderHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function __invoke(CreateOrder $command): void
    {
        $cart = $this->cartRepository->findById(CartId::create($command->cartId()));

        if ($cart === null) {
            throw new DomainException('Carrito no encontrado');
        }

        if (!$cart->isChecked()) {
            throw new DomainException('El carrito no ha sido procesado');
        }

        $order = Order::create(
            OrderId::create($command->orderId()),
            $command->userId(),
            ShippingAddress::create(
                $command->street(),
                $command->city(),
                $command->postalCode(),
                $command->country(),
                $command->recipientName(),
            ),
        );

        foreach ($cart->items() as $item) {
            $order->addItem(OrderItem::create(
                $item->product()->productId(),
                $item->product()->name(),
                Money::create($item->product()->price()->amount()),
                $item->quantity()->value(),
            ));
        }

        $this->orderRepository->save($order);
    }
}
