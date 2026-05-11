<?php

declare(strict_types=1);

namespace App\Order\Application\Query;

use App\Order\Domain\Model\OrderId;
use App\Order\Domain\Repository\OrderRepositoryInterface;
use DomainException;

final class GetOrderHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
    ) {}

    public function __invoke(GetOrder $query): GetOrderResponse
    {
        $order = $this->repository->findById(OrderId::create($query->orderId()));

        if ($order === null) {
            throw new DomainException('Orden no encontrada');
        }

        $items = array_map(fn($item) => [
            'productId'   => $item->productId(),
            'productName' => $item->productName(),
            'unitPrice'   => $item->unitPrice()->amount(),
            'quantity'    => $item->quantity(),
            'total'       => $item->total()->amount(),
        ], $order->items());

        return new GetOrderResponse(
            orderId:       $order->id()->value(),
            userId:        $order->userId(),
            status:        $order->status()->value(),
            items:         $items,
            total:         $order->total()->amount(),
            street:        $order->shippingAddress()->street(),
            city:          $order->shippingAddress()->city(),
            postalCode:    $order->shippingAddress()->postalCode(),
            country:       $order->shippingAddress()->country(),
            recipientName: $order->shippingAddress()->recipientName(),
        );
    }
}
