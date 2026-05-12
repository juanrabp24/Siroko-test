<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Http;

use App\Catalog\Application\Command\CreateProduct;
use App\Catalog\Application\Command\CreateProductHandler;
use App\Catalog\Application\Query\GetAllProducts;
use App\Catalog\Application\Query\GetAllProductsHandler;
use App\Catalog\Application\Query\GetProductById;
use App\Catalog\Application\Query\GetProductByIdHandler;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
final class ProductController extends AbstractController
{
    public function __construct(
        private readonly CreateProductHandler $createProductHandler,
        private readonly GetProductByIdHandler $getProductByIdHandler,
        private readonly GetAllProductsHandler $getAllProductsHandler,
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $products = ($this->getAllProductsHandler)(new GetAllProducts());

        return new JsonResponse(array_map(fn ($p) => (array) $p, $products));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $request->getPayload();

        foreach (['productId', 'name', 'price', 'stock'] as $field) {
            if (!$payload->has($field)) {
                return new JsonResponse(['error' => sprintf('Campo requerido: %s', $field)], 422);
            }
        }

        try {
            ($this->createProductHandler)(new CreateProduct(
                productId: $payload->getString('productId'),
                name:      $payload->getString('name'),
                price:     $payload->getInt('price'),
                stock:     $payload->getInt('stock'),
            ));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        return new JsonResponse(null, 201);
    }

    #[Route('/{productId}', methods: ['GET'])]
    public function get(string $productId): JsonResponse
    {
        try {
            $response = ($this->getProductByIdHandler)(new GetProductById(productId: $productId));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse((array) $response);
    }
}
