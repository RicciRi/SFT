<?php

namespace App\Controller;

use App\Repository\FileTransferRepository;
use App\Service\FileActionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transfer')]
final class TransferController extends AbstractController
{
    public function __construct(
        private FileTransferRepository $fileTransferRepository,
        private FileActionService $actionService
    ) {
    }

    #[Route('/{uuid}', name: 'app_transfer')]
    public function index($uuid): Response
    {
        $transfer = $this->fileTransferRepository->findOneBy(['uuid' => $uuid]);

        if (!$transfer) {
            $this->addFlash('error', 'Transfer dosn\'t exist.');

            return $this->redirectToRoute('app_send_transfers');
        }

        $user = $this->getUser();
        $company = $user->getCompany();

        if ($this->isGranted('ROLE_COMPANY_ADMIN')) {
            if ($transfer->getCompany() !== $company) {
                $this->addFlash('error', 'You have no access to this transfer');

                return $this->redirectToRoute('app_send_transfers');
            }
        } else {
            if ($transfer->getUser() !== $user) {
                $this->addFlash('error', 'You have no access to this transfer');

                return $this->redirectToRoute('app_send_transfers');
            }
        }

        $files = $transfer->getTransferredFiles();

        return $this->render('transfer/index.html.twig', [
            'transfer' => $transfer,
            'files' => $files,
        ]);
    }

    #[Route('/{uuid}/deactivate', name: 'api_deactivate_transfer')]
    public function deactivate($uuid)
    {
        $transfer = $this->fileTransferRepository->findOneBy(['uuid' => $uuid]);

        if (!$transfer) {
            $this->addFlash('error', 'Transfer dosn\'t exist.');

            return $this->redirectToRoute('app_send_transfers');
        }

        $user = $this->getUser();
        $company = $user->getCompany();

        if ($this->isGranted('ROLE_COMPANY_ADMIN')) {
            if ($transfer->getCompany() !== $company) {
                $this->addFlash('error', 'You have no access to this transfer');

                return $this->redirectToRoute('app_send_transfers');
            }
        } else {
            if ($transfer->getUser() !== $user) {
                $this->addFlash('error', 'You have no access to this transfer');

                return $this->redirectToRoute('app_send_transfers');
            }
        }

        $files = $transfer->getTransferredFiles();

        $this->actionService->deactivateTransfer($transfer, $files);

        $this->addFlash('success', 'Transfer was deactivated successfully.');
        return $this->redirectToRoute('app_send_transfers');
    }
}
