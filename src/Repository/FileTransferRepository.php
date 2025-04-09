<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\FileTransfer;
use App\Enum\TransferStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileTransfer>
 */
class FileTransferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileTransfer::class);
    }

    public function findExpiredNotMarked($now)
    {
        return $this->createQueryBuilder('t')
            ->where('t.status != :status')
            ->andWhere('t.expirationAt < :now')
            ->setParameter('status', TransferStatus::EXPIRED)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function countTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->createQueryBuilder('t')
                    ->select('COUNT(t.id)')
                    ->where('t.company = :company')
                    ->andWhere('t.createdAt BETWEEN :start AND :end')
                    ->setParameter('company', $company)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate)
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function countTotalTransferredSize(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        return $this->createQueryBuilder('t')
                    ->select('SUM(t.size)')
                    ->where('t.company = :company')
                    ->andWhere('t.createdAt BETWEEN :start AND :end')
                    ->setParameter('company', $company)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate)
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function countExpiredFiles(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        $status = TransferStatus::EXPIRED;

        return $this->createQueryBuilder('t')
                    ->select('COUNT(t.id)')
                    ->where('t.company = :company')
                    ->andWhere('t.status = :status')
                    ->andWhere('t.createdAt BETWEEN :start AND :end')
                    ->setParameter('company', $company)
                    ->setParameter('status', $status)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate)
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function getDownloadedTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        $status = TransferStatus::DOWNLOADED;

        return $this->createQueryBuilder('t')
                    ->select('COUNT(t.id)')
                    ->where('t.company = :company')
                    ->andWhere('t.status = :status')
                    ->andWhere('t.createdAt BETWEEN :start AND :end')
                    ->setParameter('company', $company)
                    ->setParameter('status', $status)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate)
                    ->getQuery()
                    ->getSingleScalarResult()
        ;
    }

    public function getUploadedTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        $status = TransferStatus::UPLOADED;

        return $this->createQueryBuilder('t')
                    ->select('COUNT(t.id)')
                    ->where('t.company = :company')
                    ->andWhere('t.status = :status')
                    ->andWhere('t.createdAt BETWEEN :start AND :end')
                    ->setParameter('company', $company)
                    ->setParameter('status', $status)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate)
                    ->getQuery()
                    ->getSingleScalarResult()
        ;
    }

    public function getDailyTransfers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT DATE(t.created_at) as day, COUNT(t.id) as count
        FROM file_transfer t
        WHERE t.company_id = :companyId
        AND t.created_at BETWEEN :start AND :end
        GROUP BY day
        ORDER BY day ASC
    ';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'companyId' => $company->getId(),
            'start' => $startDate->format('Y-m-d 00:00:00'),
            'end' => $endDate->format('Y-m-d 23:59:59'),
        ]);

        return $result->fetchAllAssociative(); // вернёт массив вида [['day' => '2025-04-01', 'count' => 3], ...]
    }
}
