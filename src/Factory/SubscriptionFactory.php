<?php

namespace App\Factory;

use App\Entity\Subscription;
use App\Enum\SubscriptionPeriod;
use App\Enum\SubscriptionStatus;
use App\Enum\SubscriptionType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Subscription>
 */
final class SubscriptionFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Subscription::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'period' => SubscriptionPeriod::MONTHLY,
            'startDate' => new \DateTimeImmutable(),
            'status' => SubscriptionStatus::ACTIVE,
            'type' => SubscriptionType::BUSINESS,
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Subscription $subscription): void {})
        ;
    }
}
