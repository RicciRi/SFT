<?php

namespace App\Service;

use App\Entity\Company;
use App\Repository\FileTransferRepository;
use App\Repository\UserRepository;

class AnalyticsService
{
    public function __construct(
        private FileTransferRepository $fileTransferRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function getTotalTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): int
    {
        return $this->fileTransferRepository->countTransfers($company, $startDate, $endDate);
    }

    public function getTotalTransferredSize(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->fileTransferRepository->countTotalTransferredSize($company, $startDate, $endDate);
    }

    public function getExpiringFiles(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): int
    {
        return $this->fileTransferRepository->countExpiredFiles($company, $startDate, $endDate);
    }

    public function getTopFiveUsers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->userRepository->getTopFiveUsers($company, $startDate, $endDate);
    }

    public function getDownloadedTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->fileTransferRepository->getDownloadedTransfers($company, $startDate, $endDate);
    }

    public function getUploadedTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->fileTransferRepository->getUploadedTransfers($company, $startDate, $endDate);
    }

    public function getDailyTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->fileTransferRepository->getDailyTransfers($company, $startDate, $endDate);
    }

    public function getDeletedTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->fileTransferRepository->getDeletedTransfers($company, $startDate, $endDate);
    }
}
