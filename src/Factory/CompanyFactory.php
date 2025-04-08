<?php

namespace App\Factory;

use App\Entity\Company;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Company>
 */
final class CompanyFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Company::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'contactEmail' => self::faker()->email(),
            'name' => self::faker()->company(),
            'address' => self::faker()->address(),
            'phone' => self::faker()->phoneNumber(),
            'website' => self::faker()->domainName(),
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Company $company): void {})
        ;
    }
}
