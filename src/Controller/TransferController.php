<?php

namespace App\Controller;

use App\Repository\FileTransferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransferController extends AbstractController
{
    public function __construct(
        private FileTransferRepository $fileTransferRepository,
    ) {
    }

    #[Route('/transfer/{uuid}', name: 'app_transfer')]
    public function index($uuid): Response
    {
        $transfer = $this->fileTransferRepository->findOneBy(['uuid' => $uuid]);

        if (!$transfer) {
            $this->addFlash('error', 'Transfer dosn\'t exist.');

            return $this->redirectToRoute('app_send_transfers');
        }

        //        dd($transfer->getCompany());

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
}
