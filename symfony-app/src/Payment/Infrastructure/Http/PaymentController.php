<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Http;

use App\Payment\Application\Command\ProcessPayment;
use App\Payment\Application\Command\ProcessPaymentHandler;
use App\Payment\Application\Query\GetPayment;
use App\Payment\Application\Query\GetPaymentHandler;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payments')]
final class PaymentController extends AbstractController
{
    public function __construct(
        private readonly ProcessPaymentHandler $processPaymentHandler,
        private readonly GetPaymentHandler $getPaymentHandler,
    ) {}

    #[Route('', methods: ['POST'])]
    public function process(Request $request): JsonResponse
    {
        $payload = $request->getPayload();

        foreach (['paymentId', 'orderId', 'amount'] as $field) {
            if (!$payload->has($field)) {
                return new JsonResponse(['error' => sprintf('Campo requerido: %s', $field)], 422);
            }
        }

        try {
            ($this->processPaymentHandler)(new ProcessPayment(
                paymentId: $payload->getString('paymentId'),
                orderId:   $payload->getString('orderId'),
                amount:    $payload->getInt('amount'),
            ));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        return new JsonResponse(null, 201);
    }

    #[Route('/{paymentId}', methods: ['GET'])]
    public function get(string $paymentId): JsonResponse
    {
        try {
            $response = ($this->getPaymentHandler)(new GetPayment(paymentId: $paymentId));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse((array) $response);
    }
}
