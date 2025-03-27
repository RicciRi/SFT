<?php

namespace App\Controller;

use App\Entity\FileTransfer;
use App\Repository\FileTransferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DownloadController extends AbstractController
{
    #[Route('/download/{id}', name: 'app_download')]
    public function index(FileTransfer $transfer, Request $request, FileTransferRepository $fileTransferRepository, UriSigner $uriSigner): Response
    {
        $currentUlr = $request->getUri();

        if (!$uriSigner->check($currentUlr)) {
            throw new AccessDeniedHttpException('Invalid or expired download link.');
        }

        $token = $request->query->get('token');

        $files = $transfer->getTransferredFiles();

        return $this->render('download/index.html.twig', [
            'transfer' => $transfer,
            'files' => $files,
        ]);
    }
}
