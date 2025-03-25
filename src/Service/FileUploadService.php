<?php

namespace App\Service;

use App\Entity\FileTransfer;
use App\Entity\TransferredFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class FileUploadService
{
    private string $tempDir;
    private string $uploadDir;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        // Используем единую директорию для временных и постоянных файлов
        $this->tempDir = '/tmp/file_transfers';
        $this->uploadDir = '/tmp/file_transfers';
        $this->logger = $logger;

        // Создаем директории, если они не существуют
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
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

    /**
     * Создает сущности TransferredFile без перемещения файлов.
     */
    public function persistFiles(array $fileData, FileTransfer $fileTransfer): void
    {
        foreach ($fileData as $data) {
            try {
                $tempFilename = $data['tempFilename'];
                $tempPath = $this->tempDir.'/'.$tempFilename;

                // Проверяем существование файла
                if (!file_exists($tempPath)) {
                    throw new \Exception("Файл не найден: {$tempPath}");
                }

                // Так как файл уже в постоянной директории, просто используем его
                $storedFilename = $tempFilename;

                // Создаем запись о файле в БД
                $transferredFile = new TransferredFile();
                $transferredFile->setFileTransfer($fileTransfer);
                $transferredFile->setOriginalFilename($data['originalFilename']);
                $transferredFile->setStoredFilename($storedFilename);
                $transferredFile->setFileSize((string) $data['fileSize']);
                $transferredFile->setMimeType($data['mimeType']);
                $transferredFile->setCreatedAt(new \DateTimeImmutable());

                $fileTransfer->addTransferredFile($transferredFile);

                $this->logger->info('Файл успешно сохранен в БД', [
                    'originalFilename' => $data['originalFilename'],
                    'storedFilename' => $storedFilename,
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Ошибка при сохранении файла', [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]);
                throw $e;
            }
        }
    }

    /**
     * Метод для тестирования доступа к директориям
     */
    public function testDirectoryAccess(): array
    {
        $tempTestFile = $this->tempDir.'/test_'.uniqid().'.txt';
        $permanentTestFile = $this->uploadDir.'/test_'.uniqid().'.txt';

        // Тестируем временную директорию
        $tempDirExists = is_dir($this->tempDir);
        $tempDirWritable = is_writable($this->tempDir);
        $tempFileWritten = false;
        $tempFileContent = null;

        if ($tempDirExists && $tempDirWritable) {
            file_put_contents($tempTestFile, 'Test content');
            $tempFileWritten = file_exists($tempTestFile);
            if ($tempFileWritten) {
                $tempFileContent = file_get_contents($tempTestFile);
                unlink($tempTestFile);
            }
        }

        // Тестируем постоянную директорию
        $uploadDirExists = is_dir($this->uploadDir);
        $uploadDirWritable = is_writable($this->uploadDir);
        $permanentFileWritten = false;
        $permanentFileContent = null;

        if ($uploadDirExists && $uploadDirWritable) {
            file_put_contents($permanentTestFile, 'Test content');
            $permanentFileWritten = file_exists($permanentTestFile);
            if ($permanentFileWritten) {
                $permanentFileContent = file_get_contents($permanentTestFile);
                unlink($permanentTestFile);
            }
        }

        return [
            'temp_directory' => [
                'path' => $this->tempDir,
                'exists' => $tempDirExists,
                'writable' => $tempDirWritable,
                'file_written' => $tempFileWritten,
                'file_content_ok' => 'Test content' === $tempFileContent,
            ],
            'upload_directory' => [
                'path' => $this->uploadDir,
                'exists' => $uploadDirExists,
                'writable' => $uploadDirWritable,
                'file_written' => $permanentFileWritten,
                'file_content_ok' => 'Test content' === $permanentFileContent,
            ],
        ];
    }

    /**
     * Удаляет все временные файлы по маркеру сессии.
     */
    public function cleanupTemporaryFiles(array $sessionTokens): void
    {
        // Поскольку мы не перемещаем файлы, мы не удаляем их после persist
    }
}
