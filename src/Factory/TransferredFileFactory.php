<?php

namespace App\Factory;

use App\Entity\TransferredFile;
use App\Enum\FileStatus;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<TransferredFile>
 */
final class TransferredFileFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return TransferredFile::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'createdAt' => new \DateTimeImmutable(),
            'fileSize' => self::faker()->randomNumber(),
            'mimeType' => self::faker()->mimeType(),
            'originalFilename' => self::faker()->text(20),
            'storedFilename' => self::faker()->text(20),
            'status' => FileStatus::UPLOADED,
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(TransferredFile $transferredFile): void {})
        ;
    }
}
