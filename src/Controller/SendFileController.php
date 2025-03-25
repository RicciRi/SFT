<?php

namespace App\Controller;

use App\Entity\FileTransfer;
use App\Repository\FileTransferRepository;
use App\Service\FileUploadService;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/send/file')]
final class SendFileController extends AbstractController
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileTransferRepository $fileTransferRepository,
        private readonly MessageService $messageService,
    ) {
    }

    #[Route('/', name: 'app_send_file')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function new(): Response
    {
        return $this->render('send_file/new.html.twig', [
            'max_file_size' => $this->getMaxUploadSize(),
        ]);
    }

    #[Route('/history', name: 'app_send_file_history')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function index(): Response
    {
        $user = $this->getUser();
        $fileTransfers = $this->fileTransferRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('send_file/index.html.twig', [
            'fileTransfers' => $fileTransfers,
        ]);
    }

    #[Route('/api/upload-files', name: 'api_upload_files', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function uploadFiles(Request $request): JsonResponse
    {
        $files = $request->files->get('files');

        if (!$files || empty($files)) {
            return $this->json(['error' => 'No files inside.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Обрабатываем файлы и сохраняем во временное хранилище
            $filesData = $this->fileUploadService->processTemporaryFiles($files);

            // Сохраняем данные о файлах в сессии
            $session = $request->getSession();
            $session->set('temp_file_data', $filesData);

            // Готовим данные для ответа
            $response = [];
            foreach ($filesData as $fileData) {
                $response[] = [
                    'originalFilename' => $fileData['originalFilename'],
                    'fileSize' => $this->formatFileSize($fileData['fileSize']),
                    'mimeType' => $fileData['mimeType'],
                    'sessionToken' => $fileData['sessionToken'],
                ];
            }

            return $this->json([
                'success' => true,
                'message' => 'Файлы успешно загружены во временное хранилище',
                'files' => $response,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ошибка при загрузке файлов: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/file-transfer/create', name: 'api_file_transfer_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function createFileTransfer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['recipientEmail']) || !isset($data['subject']) || !isset($data['message'])) {
            return $this->json(['error' => 'Не все обязательные поля заполнены'], Response::HTTP_BAD_REQUEST);
        }

        $session = $request->getSession();
        $filesData = $session->get('temp_file_data', []);

        if (empty($filesData)) {
            return $this->json(['error' => 'Не найдены загруженные файлы'], Response::HTTP_BAD_REQUEST);
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

            // Устанавливаем срок действия (например, 7 дней)
            $expirationAt = new \DateTimeImmutable('+7 days');
            $fileTransfer->setExpirationAt($expirationAt);

            $fileTransfer->setStatus('pending');

            // Перемещаем файлы из временного хранилища в постоянное
            $this->fileUploadService->persistFiles($filesData, $fileTransfer);

            // Сохраняем в базу данных
            $this->entityManager->persist($fileTransfer);
            $this->entityManager->flush();

            // Очищаем данные о временных файлах
            $sessionTokens = array_map(function ($file) {
                return $file['sessionToken'];
            }, $filesData);
            $this->fileUploadService->cleanupTemporaryFiles($sessionTokens);
            $session->remove('temp_file_data');

            // Здесь можно добавить отправку уведомления получателю
            $this->messageService->sendEmail(
                $user->getEmail(),
                $data['recipientEmail'],
                $data['subject'],
                $data['message']);

            return $this->json([
                'success' => true,
                'message' => 'Файлы успешно отправлены',
                'id' => $fileTransfer->getId(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ошибка при обработке запроса: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Форматирует размер файла в удобочитаемый формат
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Получает максимальный размер загружаемого файла из настроек PHP.
     */
    private function getMaxUploadSize(): string
    {
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));

        return $this->formatFileSize(min($postMaxSize, $uploadMaxFilesize));
    }

    /**
     * Парсит строку размера (например, "8M") в байты.
     */
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
