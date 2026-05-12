<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Http;

use App\Order\Application\Command\CreateOrder;
use App\Order\Application\Command\CreateOrderHandler;
use App\Order\Application\Query\GetOrder;
use App\Order\Application\Query\GetOrderHandler;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/orders')]
final class OrderController extends AbstractController
{
    public function __construct(
        private readonly CreateOrderHandler $createOrderHandler,
        private readonly GetOrderHandler $getOrderHandler,
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $request->getPayload();

        foreach (['orderId', 'cartId', 'userId', 'street', 'city', 'postalCode', 'country', 'recipientName'] as $field) {
            if (!$payload->has($field)) {
                return new JsonResponse(['error' => sprintf('Campo requerido: %s', $field)], 422);
            }
        }

        try {
            ($this->createOrderHandler)(new CreateOrder(
                orderId:       $payload->getString('orderId'),
                cartId:        $payload->getString('cartId'),
                userId:        $payload->getString('userId'),
                street:        $payload->getString('street'),
                city:          $payload->getString('city'),
                postalCode:    $payload->getString('postalCode'),
                country:       $payload->getString('country'),
                recipientName: $payload->getString('recipientName'),
            ));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        return new JsonResponse(null, 201);
    }

    #[Route('/{orderId}', methods: ['GET'])]
    public function get(string $orderId): JsonResponse
    {
        try {
            $response = ($this->getOrderHandler)(new GetOrder(orderId: $orderId));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse((array) $response);
    }
}
