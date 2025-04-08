<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private ?UserPasswordHasherInterface $passwordHasher = null,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'createdAt' => new \DateTimeImmutable(),
            'email' => self::faker()->email(),
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'isMainAccount' => false,
            'isVerified' => true,
            'isActive' => true,
            'password' => '123123123',
            'roles' => ['ROLE_COMPANY_EMPLOYEE'],
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (User $user) {
                if (null !== $this->passwordHasher) {
                    $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
                }
            });
    }
}
