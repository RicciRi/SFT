<?php

namespace App\Controller;

use App\Enum\SubscriptionType;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
final class PaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaymentService $paymentService,
    ) {
    }

    #[Route('/pro', name: 'app_payment_pro')]
    public function pro(): Response
    {
        return $this->render('payment/pro.html.twig');
    }

    #[Route('/create/pro', name: 'api_create_pro')]
    public function createPro()
    {
        $amount = '10.00';
        $currency = 'USD';
        $paymentMethod = 'card';
        $transactionId = '123123123';
        $status = 'success';
        $receiptUrl = 'https://pay.example.com/';
        $subscription = $this->getUser()->getCompany()->getActiveSubscription();

        $this->paymentService->createPayment(
            SubscriptionType::PRO,
            $amount,
            $currency,
            $transactionId,
            $status,
            $receiptUrl,
            $paymentMethod,
            $subscription,
        );

        return $this->render('price/index.html.twig');
    }

    #[Route('/business', name: 'app_payment_business')]
    public function business(): Response
    {
        return $this->render('payment/business.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }

    #[Route('/create/business', name: 'api_create_business')]
    public function createBusiness()
    {
        $amount = '40.00';
        $currency = 'USD';
        $paymentMethod = 'card';
        $transactionId = '123123123';
        $status = 'success';
        $receiptUrl = 'https://pay.example.com/';
        $subscription = $this->getUser()->getCompany()->getActiveSubscription();

        $this->paymentService->createPayment(
            SubscriptionType::BUSINESS,
            $amount,
            $currency,
            $transactionId,
            $status,
            $receiptUrl,
            $paymentMethod,
            $subscription,
        );

        return $this->render('price/index.html.twig');
    }
}
