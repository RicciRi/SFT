<?php

namespace App\Command;

use App\Enum\TransferStatus;
use App\Repository\FileTransferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:cleanup-expired-files',
    description: 'Mark expired file transfers & files and remove associated files.',
)]
class CleanupExpiredFilesCommand extends Command
{
    private string $tempDir;
    private string $uploadDir;

    public function __construct(
        private FileTransferRepository $fileTransferRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        string $uploadDir,
    ) {
        parent::__construct();
        $this->uploadDir = $uploadDir;
        $this->tempDir = $uploadDir;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $filesystem = new Filesystem();

        $expiredTransfers = $this->fileTransferRepository->findExpiredNotMarked($now);

        foreach ($expiredTransfers as $transfer) {
            if (TransferStatus::UPLOADED === $transfer->getStatus()) {
                $transfer->setStatus(TransferStatus::EXPIRED);
            }
            $transfer->setIsExpired(true);

            foreach ($transfer->getTransferredFiles() as $file) {
                $filePath = $this->tempDir.'/'.$file->getStoredFilename();

                if (TransferStatus::UPLOADED === $transfer->getStatus()) {
                    $file->markAsExpired();
                }


                if ($filesystem->exists($filePath)) {
                    $this->logger->info('FILE WAS REMOVED! - '.$filePath);
                    $filesystem->remove($filePath);
                } else {
                    $this->logger->info('FILE WAS NOT NOT NOT REMOVED! -');
                }
            }
        }

        $this->entityManager->flush();

        $output->writeln(count($expiredTransfers).' transfers marked as expired and files deleted.');

        return Command::SUCCESS;
    }
}
