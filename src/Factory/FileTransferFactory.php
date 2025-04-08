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
        return [
            'createdAt' => new \DateTimeImmutable(),
            'expirationAt' => (new \DateTimeImmutable())->add(new \DateInterval('P30D')),
            'message' => self::faker()->text(60),
            'recipientEmail' => self::faker()->email(),
            'status' => TransferStatus::UPLOADED,
            'subject' => self::faker()->text(30),
            'size' => self::faker()->randomNumber(),
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(FileTransfer $fileTransfer): void {})
        ;
    }
}
