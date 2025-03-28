<?php

namespace App\Controller;

use App\Entity\FileTransfer;
use App\Entity\TransferredFile;
use App\Repository\FileTransferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use ZipStream\Option\Archive as ArchiveOptions;
use ZipStream\ZipStream;

final class DownloadController extends AbstractController
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private UriSigner $uriSigner,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/download/{id}', name: 'app_download')]
    public function index(FileTransfer $transfer, Request $request, FileTransferRepository $fileTransferRepository, UriSigner $uriSigner): Response
    {
        $currentUlr = $request->getUri();

        if (!$uriSigner->check($currentUlr)) {
            throw new AccessDeniedHttpException('Invalid or expired download link.');
        }

        $files = $transfer->getTransferredFiles();

        $downloadSize = 0;

        foreach ($files as $file) {
            $downloadSize += $file->getFileSize();
        }

        return $this->render('download/index.html.twig', [
            'transfer' => $transfer,
            'files' => $files,
            'download_links' => $this->generateSignedFileLinks($files),
            'download_all_link' => $this->generateDownloadAllLink($transfer),
            'download_size' => $downloadSize,
        ]);
    }

    #[Route('/download/file/{id}', name: 'app_download_file', methods: ['GET'])]
    public function downloadFile(TransferredFile $transferredFile, Request $request, UriSigner $uriSigner): BinaryFileResponse
    {
        $url = $request->getUri();

        if (!$uriSigner->check($url)) {
            throw new AccessDeniedHttpException('Invalid or expired link.');
        }

        if (!$transferredFile) {
            throw new BadRequestHttpException('File doesn\'t exist.');
        }

        $expDate = $transferredFile->getFileTransfer()->getExpirationAt();

        if ($expDate < new \DateTimeImmutable()) {
            throw new AccessDeniedHttpException('Link expired.');
        }

        $storedFilename = $request->query->get('storedFilename');
        if (!$storedFilename) {
            throw new BadRequestHttpException('Missing stored filename.');
        }

        if ($transferredFile->getStoredFilename() !== $storedFilename) {
            throw new BadRequestHttpException('Incorrect file name path!');
        }

        $uploadDir = $this->getParameter('upload_dir');
        $filePath = $uploadDir.'/'.$storedFilename;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('File does not exist on the server.');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $transferredFile->getOriginalFilename(),
        );

        $transfer = $transferredFile->getFileTransfer();

        $transfer->markAsDownloaded();

        $this->entityManager->flush();

        return $response;
    }

    #[Route('/download/{id}/all', name: 'app_download_all')]
    public function downloadAll(FileTransfer $transfer, Request $request, UriSigner $uriSigner): StreamedResponse
    {
        $url = $request->getUri();

        if (!$uriSigner->check($url)) {
            throw new AccessDeniedHttpException('Invalid or expired link.');
        }

        if (!$transfer) {
            throw new BadRequestHttpException('Transfer doesn\'t exist!');
        }

        if ($transfer->getExpirationAt() < new \DateTimeImmutable()) {
            throw new AccessDeniedHttpException('Link expired.');
        }

        $uploadDir = $this->getParameter('upload_dir');

        $files = $transfer->getTransferredFiles();

        $response = new StreamedResponse(function () use ($files, $uploadDir) {
            $options = new ArchiveOptions();
            $options->setSendHttpHeaders(true);

            $zip = new ZipStream('files.zip', $options);

            foreach ($files as $file) {
                $filePath = $uploadDir.'/'.$file->getStoredFilename();
                if (file_exists($filePath)) {
                    $zip->addFileFromPath($file->getOriginalFilename(), $filePath);
                }
            }

            $zip->finish();
        });
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="files.zip"');

        $transfer->markAsDownloaded();

        $this->entityManager->flush();

        return $response;
    }

    private function generateSignedFileLink(TransferredFile $file): string
    {
        $url = $this->router->generate(
            'app_download_file',
            [
                'id' => $file->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $urlWithQuery = $url.'?storedFilename='.urlencode($file->getStoredFilename());

        return $this->uriSigner->sign($urlWithQuery);
    }

    private function generateSignedFileLinks(iterable $files): array
    {
        $links = [];
        foreach ($files as $file) {
            $links[$file->getId()] = $this->generateSignedFileLink($file);
        }

        return $links;
    }

    private function generateDownloadAllLink(FileTransfer $transfer): string
    {
        $url = $this->router->generate(
            'app_download_all',
            ['id' => $transfer->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->uriSigner->sign($url);
    }
}
