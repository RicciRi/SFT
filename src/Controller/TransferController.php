<?php

namespace App\Controller;

use App\Entity\FileTransfer;
use App\Repository\FileTransferRepository;
use App\Service\FileActionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/transfer')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
final class TransferController extends AbstractController
{
    public function __construct(
        private FileTransferRepository $fileTransferRepository,
        private FileActionService $actionService,
    ) {
    }

    #[Route('/{uuid}', name: 'app_transfer')]
    public function index(string $uuid): Response
    {
        if (!$transfer = $this->fetchAndAuthorizeTransfer($uuid)) {
            return $this->redirectToRoute('app_send_transfers');
        }

        $files = $transfer->getTransferredFiles();

        return $this->render('transfer/index.html.twig', [
            'transfer' => $transfer,
            'files' => $files,
        ]);
    }

    #[Route('/{uuid}/deactivate', name: 'api_deactivate_transfer')]
    public function deactivate(string $uuid): Response
    {
        if (!$transfer = $this->fetchAndAuthorizeTransfer($uuid)) {
            return $this->redirectToRoute('app_send_transfers');
        }

        $files = $transfer->getTransferredFiles();
        $this->actionService->deactivateTransfer($transfer, $files);

        $this->addFlash('success', 'Transfer was deactivated successfully.');

        return $this->redirectToRoute('app_send_transfers');
    }

    private function fetchAndAuthorizeTransfer(string $uuid): ?FileTransfer
    {
        $transfer = $this->fileTransferRepository->findOneBy(['uuid' => $uuid]);

        if (!$transfer) {
            $this->addFlash('error', 'Transfer doesn\'t exist.');

            return null;
        }

        $user = $this->getUser();

        if (
            $this->isGranted('ROLE_COMPANY_ADMIN') && $transfer->getCompany() !== $user->getCompany()
            || !$this->isGranted('ROLE_COMPANY_ADMIN') && $transfer->getUser() !== $user
        ) {
            $this->addFlash('error', 'You have no access to this transfer');

            return null;
        }

        return $transfer;
    }
}
