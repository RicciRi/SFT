<?php

namespace App\Controller;

use App\Service\AnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_COMPANY_ADMIN") or user.isMainAccount()'))]
final class AnalyticsController extends AbstractController
{
    public function __construct(
        private AnalyticsService $analyticsService,
    ) {
    }

    #[Route('/analytics', name: 'app_analytics')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $company = $user->getCompany();

        [$startDate, $endDate] = $this->getCurrentMonthRange();

        return $this->render('analytics/index.html.twig', [
            'totalTransferredSize' => $this->analyticsService->getTotalTransferredSize($company, $startDate, $endDate) ?? 0,
            'totalTransfers' => $this->analyticsService->getTotalTransfers($company, $startDate, $endDate),
            'downloadedTransfers' => $this->analyticsService->getDownloadedTransfers($company, $startDate, $endDate),
            'uploadedTransfers' => $this->analyticsService->getUploadedTransfers($company, $startDate, $endDate),
            'deletedTransfers' => $this->analyticsService->getDeletedTransfers($company, $startDate, $endDate),
            'dailyTransfers' => $this->analyticsService->getDailyTransfers($company, $startDate, $endDate),
            'expiredFiles' => $this->analyticsService->getExpiringFiles($company, $startDate, $endDate),
            'topFiveUsers' => $this->analyticsService->getTopFiveUsers($company, $startDate, $endDate),
        ]);
    }

    private function getCurrentMonthRange(): array
    {
        $startDate = new \DateTimeImmutable('first day of this month midnight');
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

        return [$startDate, $endDate];
    }
}
