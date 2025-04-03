<?php

namespace App\Controller;

use App\Entity\FileTransfer;
use App\Enum\TransferStatus;
use App\Repository\FileTransferRepository;
use App\Service\FileUploadService;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/send')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
final class SendFileController extends AbstractController
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileTransferRepository $fileTransferRepository,
        private readonly MessageService $messageService,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/', name: 'app_send')]
    public function new(): Response
    {
        return $this->render('send/new.html.twig', [
            'max_file_size' => $this->getMaxUploadSize(),
        ]);
    }

    #[Route('/transfers', name: 'app_send_transfers')]
    public function index(FileTransferRepository $fileTransferRepository, Request $request): Response
    {
        $user = $this->getUser();

        $qb = $fileTransferRepository->createQueryBuilder('t')
                                     ->select('t')
                                     ->where('t.user = :user')
                                     ->setParameter('user', $user);

        $status = $request->query->get('status');

        if ($status && in_array($status, ['uploaded', 'downloaded'], true)) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $status);
        }

        $search = $request->query->get('q');
        if ($search) {
            $qb->andWhere(
                $qb->expr()->orX(
                    't.recipientEmail LIKE :search',
                    't.subject LIKE :search',
                    't.message LIKE :search'
                )
            )
               ->setParameter('search', '%'.$search.'%');
        }

        // Фильтр по дате (last day/week/month)
        $dateFilter = $request->query->get('date_filter');
        if ($dateFilter) {
            $date = new \DateTimeImmutable();

            switch ($dateFilter) {
                case 'day':
                    $since = $date->modify('-1 day');
                    break;
                case 'week':
                    $since = $date->modify('-7 days');
                    break;
                case 'month':
                    $since = $date->modify('-1 month');
                    break;
                default:
                    $since = null;
            }

            if ($since) {
                $qb->andWhere('t.createdAt >= :since')
                   ->setParameter('since', $since);
            }
        }

        // Сортировка по дате и размеру отдельно
        $dateOrder = $request->query->get('date_order');
        $sizeOrder = $request->query->get('size_order');

        if ('newest' === $dateOrder) {
            $qb->addOrderBy('t.createdAt', 'DESC');
        } elseif ('oldest' === $dateOrder) {
            $qb->addOrderBy('t.createdAt', 'ASC');
        }

        if ('largest' === $sizeOrder) {
            $qb->addOrderBy('t.size', 'DESC');
        } elseif ('smallest' === $sizeOrder) {
            $qb->addOrderBy('t.size', 'ASC');
        }

        // Пагинация
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', 10);
        $offset = ($page - 1) * $limit;

        $totalItems = (clone $qb)
            ->select('COUNT(t.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $transfers = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $totalPages = ceil($totalItems / $limit);

        return $this->render('send/index.html.twig', [
            'transfers' => $transfers,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'date_filter' => $dateFilter,
            'date_order' => $dateOrder,
            'size_order' => $sizeOrder,
            'limit' => $limit,
            'status' => $status,
        ]);
    }

    #[Route('/api/reset-files', name: 'api_reset_files', methods: ['POST'])]
    public function resetFiles(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->remove('temp_file_data');

        $this->logger->info('Сессия очищена');

        return $this->json([
            'success' => true,
        ]);
    }

    #[Route('/api/remove-file/{token}', name: 'api_remove_file', methods: ['DELETE'])]
    public function removeFile(string $token, Request $request): JsonResponse
    {
        $session = $request->getSession();
        $filesData = $session->get('temp_file_data', []);

        $this->logger->info('Request to delete files', [
            'token' => $token,
            'count_of_files_to_delete' => count($filesData),
        ]);

        $updatedFilesData = array_filter($filesData, function ($file) use ($token) {
            return $file['sessionToken'] !== $token;
        });

        $updatedFilesData = array_values($updatedFilesData);

        $session->set('temp_file_data', $updatedFilesData);

        $this->logger->info('File deleted from session', [
            'count_files_after_deleted' => count($updatedFilesData),
        ]);

        return $this->json([
            'success' => true,
            'remaining_files' => count($updatedFilesData),
        ]);
    }

    #[Route('/api/upload-files', name: 'api_upload_files', methods: ['POST'])]
    public function uploadFiles(Request $request): JsonResponse
    {
        $files = $request->files->get('files');

        if (!$files || empty($files)) {
            return $this->json(['error' => 'No files inside.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $session = $request->getSession();

            $existingFilesData = $session->get('temp_file_data', []);

            $this->logger->info('Files in session:', [
                'count' => count($existingFilesData),
            ]);

            $newFilesData = $this->fileUploadService->processTemporaryFiles($files);

            $allFilesData = array_merge($existingFilesData, $newFilesData);

            $session->set('temp_file_data', $allFilesData);

            $this->logger->info('Обновлено в сессии', [
                'общее_количество' => count($allFilesData),
            ]);

            $response = [];
            foreach ($newFilesData as $fileData) {
                $response[] = [
                    'originalFilename' => $fileData['originalFilename'],
                    'fileSize' => $this->formatFileSize($fileData['fileSize']),
                    'mimeType' => $fileData['mimeType'],
                    'sessionToken' => $fileData['sessionToken'],
                ];
            }

            return $this->json([
                'success' => true,
                'message' => 'Files successfully saved!',
                'files' => $response,
                'total_files' => count($allFilesData),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error to upload files', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->json([
                'error' => 'Error upload files: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/file-transfer/create', name: 'api_file_transfer_create', methods: ['POST'])]
    public function createFileTransfer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['recipientEmail']) || !isset($data['subject']) || !isset($data['message']) || !isset($data['expirationAt'])) {
            return $this->json(['error' => 'Не все обязательные поля заполнены'], Response::HTTP_BAD_REQUEST);
        }

        $session = $request->getSession();
        $filesData = $session->get('temp_file_data', []);

        $this->logger->info('Files date info', [
            'count_files' => count($filesData),
            'name_files' => array_map(function ($file) {
                return $file['originalFilename'];
            }, $filesData),
        ]);

        if (empty($filesData)) {
            return $this->json(['error' => 'Can not find uploaded files'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();

        try {
            // Создаем новую передачу файлов
            $fileTransfer = new FileTransfer();
            $fileTransfer->setUser($user);
            $fileTransfer->setRecipientEmail($data['recipientEmail']);
            $fileTransfer->setSubject($data['subject']);
            $fileTransfer->setMessage($data['message']);
            $fileTransfer->setCreatedAt(new \DateTimeImmutable());

            $expDate = $data['expirationAt'];

            $expDateImmutable = \DateTimeImmutable::createFromFormat('Y-m-d', $expDate);

            if (!$expDateImmutable) {
                throw new \Exception('Not valid date of expiration');
            }

            $fileTransfer->setExpirationAt($expDateImmutable);

            $this->logger->info($fileTransfer->getUuid());

            $fileTransfer->setStatus(TransferStatus::UPLOADED);

            $this->entityManager->persist($fileTransfer);

            $this->fileUploadService->persistFiles($filesData, $fileTransfer, $this->entityManager);

            $fileTransfer->setSize($fileTransfer->calculateTotalSize());
            $this->entityManager->flush();

            $this->logger->info('Files after upload', [
                'count_files' => count($fileTransfer->getTransferredFiles()),
                'id_upload' => $fileTransfer->getId(),
            ]);

            $sessionTokens = array_map(function ($file) {
                return $file['sessionToken'];
            }, $filesData);
            $this->fileUploadService->cleanupTemporaryFiles($sessionTokens);
            $session->remove('temp_file_data');

            $this->messageService->sendFileSentNotification(
                $user,
                $expDateImmutable,
                $data['recipientEmail'],
                $data['subject'],
                $data['message'],
                $fileTransfer
            );

            return $this->json([
                'success' => true,
                'message' => 'Files successfully uploaded!',
                'id' => $fileTransfer->getId(),
                'files_count' => count($fileTransfer->getTransferredFiles()),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->json([
                'error' => 'Error: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    private function getMaxUploadSize(): string
    {
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));

        return $this->formatFileSize(min($postMaxSize, $uploadMaxFilesize));
    }

    private function parseSize(string $size): int
    {
        $unit = preg_replace('/[^a-zA-Z]/', '', $size);
        $size = (int) preg_replace('/[^0-9]/', '', $size);

        if ($unit) {
            return match (strtoupper($unit)) {
                'K' => $size * 1024,
                'M' => $size * 1024 * 1024,
                'G' => $size * 1024 * 1024 * 1024,
                default => $size,
            };
        }

        return $size;
    }
}
