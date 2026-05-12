<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Http;

use App\Cart\Application\Command\AddItemToCart;
use App\Cart\Application\Command\AddItemToCartHandler;
use App\Cart\Application\Command\CheckoutCart;
use App\Cart\Application\Command\CheckoutCartHandler;
use App\Cart\Application\Command\RemoveItemFromCart;
use App\Cart\Application\Command\RemoveItemFromCartHandler;
use App\Cart\Application\Query\GetCart;
use App\Cart\Application\Query\GetCartHandler;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
final class CartController extends AbstractController
{
    public function __construct(
        private readonly AddItemToCartHandler $addItemHandler,
        private readonly RemoveItemFromCartHandler $removeItemHandler,
        private readonly CheckoutCartHandler $checkoutHandler,
        private readonly GetCartHandler $getCartHandler,
    ) {}

    #[Route('/{cartId}/items', methods: ['POST'])]
    public function addItem(string $cartId, Request $request): JsonResponse
    {
        $payload = $request->getPayload();

        foreach (['userId', 'productId', 'productName', 'productPrice', 'quantity'] as $field) {
            if (!$payload->has($field)) {
                return new JsonResponse(['error' => sprintf('Campo requerido: %s', $field)], 422);
            }
        }

        try {
            ($this->addItemHandler)(new AddItemToCart(
                cartId:       $cartId,
                userId:       $payload->getString('userId'),
                productId:    $payload->getString('productId'),
                productName:  $payload->getString('productName'),
                productPrice: $payload->getInt('productPrice'),
                quantity:     $payload->getInt('quantity'),
            ));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        return new JsonResponse(null, 201);
    }

    #[Route('/{cartId}/items/{productId}', methods: ['DELETE'])]
    public function removeItem(string $cartId, string $productId): JsonResponse
    {
        try {
            ($this->removeItemHandler)(new RemoveItemFromCart(
                cartId:    $cartId,
                productId: $productId,
            ));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        return new JsonResponse(null, 200);
    }

    #[Route('/{cartId}/checkout', methods: ['POST'])]
    public function checkout(string $cartId): JsonResponse
    {
        try {
            ($this->checkoutHandler)(new CheckoutCart(cartId: $cartId));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        return new JsonResponse(null, 200);
    }

    #[Route('/{cartId}', methods: ['GET'])]
    public function getCart(string $cartId): JsonResponse
    {
        try {
            $response = ($this->getCartHandler)(new GetCart(cartId: $cartId));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse((array) $response);
    }
}
