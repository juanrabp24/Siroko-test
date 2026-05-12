<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StoreController extends AbstractController
{
    #[Route('/', name: 'store_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('store/index.html.twig');
    }

    #[Route('/checkout', name: 'store_checkout', methods: ['GET'])]
    public function checkout(): Response
    {
        return $this->render('checkout/index.html.twig');
    }
}
