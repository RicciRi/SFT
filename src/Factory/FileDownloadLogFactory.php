<?php

namespace App\Factory;

use App\Entity\FileDownloadLog;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<FileDownloadLog>
 */
final class FileDownloadLogFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return FileDownloadLog::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(FileDownloadLog $fileDownloadLog): void {})
        ;
    }
}
