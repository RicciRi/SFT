<?php

namespace App\Factory;

use App\Entity\FileTransfer;
use App\Enum\TransferStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<FileTransfer>
 */
final class FileTransferFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return FileTransfer::class;
    }

    protected function defaults(): array|callable
    {
        $now = new \DateTimeImmutable();
        $startOfMonth = $now->modify('first day of this month')->setTime(0, 0);
        $endOfMonth = $now->modify('last day of this month')->setTime(23, 59, 59);

        $randomTimestamp = mt_rand($startOfMonth->getTimestamp(), $endOfMonth->getTimestamp());
        $createdAt = (new \DateTimeImmutable())->setTimestamp($randomTimestamp);

        $min = 1.9 * 1024 * 1024 * 1024; // ~2046820352
        $max = 2.1 * 1024 * 1024 * 1024; // ~2254857830

        return [
            'createdAt' => $createdAt,
            'expirationAt' => $createdAt->add(new \DateInterval('P2D')),
            'message' => self::faker()->text(60),
            'recipientEmail' => self::faker()->email(),
            'status' => TransferStatus::UPLOADED,
            'isDeleted' => false,
            'isExpired' => false,
            'subject' => self::faker()->text(30),
            'size' => self::faker()->numberBetween((int) $min, (int) $max),
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(FileTransfer $fileTransfer): void {})
        ;
    }
}
