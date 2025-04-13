<?php

namespace App\Service;

use App\Entity\FileTransfer;
use App\Enum\FileStatus;
use App\Enum\TransferStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileActionService
{
    private string $tempDir;
    private string $uploadDir;

    public function __construct(
        private EntityManagerInterface $entityManager,
        string $uploadDir,
    ) {
        $this->uploadDir = $uploadDir;
        $this->tempDir = $uploadDir;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    public function deactivateTransfer(FileTransfer $transfer, $files)
    {
        //        $transfer->setStatus(TransferStatus::DELETED);
        $transfer->setIsExpired(true);
        $transfer->setIsDeleted(true);
        $filesystem = new Filesystem();

        foreach ($files as $file) {
            //            $file->setStatus(FileStatus::DELETED);

            $filePath = $this->tempDir.'/'.$file->getStoredFilename();
            if ($filesystem->exists($filePath)) {
                $filesystem->remove($filePath);
            }
        }

        $this->entityManager->flush();
    }
}
