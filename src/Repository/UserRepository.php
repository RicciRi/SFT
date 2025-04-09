<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getTopFiveUsers(Company $company, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('u')
                    ->select('u AS user, COUNT(t.id) AS transferCount')
                    ->leftJoin('u.fileTransfers', 't')
                    ->where('u.company = :company')
                    ->andWhere('t.createdAt BETWEEN :start AND :end')
                    ->groupBy('u.id')
                    ->orderBy('transferCount', 'DESC')
                    ->setMaxResults(5)
                    ->setParameter('company', $company)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate)
                    ->getQuery()
                    ->getResult();
    }
}
