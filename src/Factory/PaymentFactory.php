<?php

namespace App\Factory;

use App\Entity\Payment;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Payment>
 */
final class PaymentFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Payment::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'amount' => self::faker()->randomFloat(),
            'currency' => 'USD',
            'paymentDate' => new \DateTimeImmutable(),
            'paymentMethod' => 'card',
            'status' => 'success',
            'transactionId' => self::faker()->uuid(),
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Payment $payment): void {})
        ;
    }
}
