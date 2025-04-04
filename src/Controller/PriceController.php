<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PriceController extends AbstractController
{
    #[Route('/price', name: 'app_price')]
    #[IsGranted(new Expression('not is_granted("ROLE_COMPANY_EMPLOYEE")'))]
    public function index(): Response
    {
        return $this->render('price/index.html.twig', [
            'controller_name' => 'PriceController',
        ]);
    }
}
