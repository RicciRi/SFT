<?php

namespace App\Service;

use App\Entity\FileTransfer;
use App\Entity\TransferredFile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class FileUploadService
{
    private string $tempDir;
    private string $uploadDir;
    private LoggerInterface $logger;

    public function __construct(string $uploadDir, LoggerInterface $logger)
    {
        $this->uploadDir = $uploadDir;
        $this->tempDir = $uploadDir;
        $this->logger = $logger;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    /**
     * Временно сохраняет файлы и возвращает метаданные.
     */
    public function processTemporaryFiles(array $files): array
    {
        $fileData = [];

        foreach ($files as $file) {
            try {
                // Генерируем токен сессии загрузки и имя файла
                $sessionToken = Uuid::v4()->toRfc4122();
                $originalFilename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $mimeType = $file->getMimeType() ?: 'application/octet-stream';

                // Генерируем уникальное имя файла
                $tempFilename = $sessionToken.($extension ? '.'.$extension : '');
                $tempPath = $this->tempDir.'/'.$tempFilename;

                // Прямое копирование файла
                if (!copy($file->getRealPath(), $tempPath)) {
                    throw new \Exception("Не удалось скопировать файл в {$tempPath}");
                }

                // Проверяем, существует ли файл и доступен ли он для чтения
                if (!file_exists($tempPath)) {
                    throw new \Exception("Файл не существует после копирования: {$tempPath}");
                }

                if (!is_readable($tempPath)) {
                    throw new \Exception("Файл существует, но не доступен для чтения: {$tempPath}");
                }

                // Логируем успешное сохранение
                $this->logger->info('Файл успешно сохранен', [
                    'tempPath' => $tempPath,
                    'originalFilename' => $originalFilename,
                    'fileSize' => filesize($tempPath),
                ]);

                $fileData[] = [
                    'tempFilename' => $tempFilename,
                    'originalFilename' => $originalFilename,
                    'mimeType' => $mimeType,
                    'fileSize' => filesize($tempPath),
                    'sessionToken' => $sessionToken,
                    'extension' => $extension,
                ];
            } catch (\Exception $e) {
                $this->logger->error('Ошибка при обработке файла', [
                    'error' => $e->getMessage(),
                    'file' => $file->getClientOriginalName(),
                ]);
                throw $e;
            }
        }

        return $fileData;
    }

    public function persistFiles(array $fileData, FileTransfer $fileTransfer, EntityManagerInterface $entityManager): void
    {
        $this->logger->info('Начало обработки файлов', [
            'количество_файлов' => count($fileData),
            'file_transfer_id' => $fileTransfer->getUuid(),
        ]);

        foreach ($fileData as $index => $data) {
            try {
                $tempFilename = $data['tempFilename'];
                $tempPath = $this->tempDir.'/'.$tempFilename;

                if (!file_exists($tempPath)) {
                    throw new \Exception("Can not find file:: {$tempPath}");
                }

                $storedFilename = $tempFilename;

                $transferredFile = new TransferredFile();
                $transferredFile->setFileTransfer($fileTransfer);
                $transferredFile->setOriginalFilename($data['originalFilename']);
                $transferredFile->setStoredFilename($storedFilename);
                $transferredFile->setFileSize((string) $data['fileSize']);
                $transferredFile->setMimeType($data['mimeType']);
                $transferredFile->setCreatedAt(new \DateTimeImmutable());

                // Добавляем файл к передаче
                $fileTransfer->addTransferredFile($transferredFile);

                // Явно сохраняем каждую сущность TransferredFile
                $entityManager->persist($transferredFile);

                $this->logger->info('Файл обработан и добавлен в коллекцию', [
                    'индекс' => $index,
                    'имя_файла' => $data['originalFilename'],
                    'размер' => $data['fileSize'],
                    'id_файла' => $transferredFile->getId() ?? 'нет_id',
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Ошибка при сохранении файла', [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]);
                throw $e;
            }
        }

        // Логируем текущее состояние коллекции
        $this->logger->info('Состояние коллекции после обработки всех файлов', [
            'количество_файлов_в_коллекции' => count($fileTransfer->getTransferredFiles()),
        ]);
    }

    /**
     * Удаляет все временные файлы по маркеру сессии.
     */
    public function cleanupTemporaryFiles(array $sessionTokens): void
    {
        // Поскольку мы не перемещаем файлы, мы не удаляем их после persist
    }
}
