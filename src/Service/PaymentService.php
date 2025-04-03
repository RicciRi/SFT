<?php

namespace App\Service;

use App\Entity\Payment;
use App\Entity\Subscription;
use App\Enum\SubscriptionPeriod;
use App\Enum\SubscriptionStatus;
use App\Enum\SubscriptionType;
use Doctrine\ORM\EntityManagerInterface;

class PaymentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createPayment(
        SubscriptionType $subscriptionType,
        string $amount,
        string $currency,
        string $paymentMethod,
        string $transactionId,
        string $status,
        string $receiptUrl,
        Subscription $oldSubscription,
    ): void {
        $date = new \DateTimeImmutable();
        $company = $oldSubscription->getCompany();

        $payment = new Payment();
        $payment->setAmount($amount);
        $payment->setCurrency($currency);
        $payment->setPaymentMethod($paymentMethod);
        $payment->setPaymentDate($date);
        $payment->setTransactionId($transactionId);
        $payment->setStatus($status);
        $payment->setReceiptUrl($receiptUrl);

        $subscription = new Subscription();
        $subscription->setCompany($company);
        $subscription->setStartDate($date);
        $subscription->setEndDate($date->modify('+30 days'));
        $subscription->setStatus(SubscriptionStatus::ACTIVE);
        $subscription->setType($subscriptionType);
        $subscription->setPeriod(SubscriptionPeriod::MONTHLY);
        $subscription->addPayment($payment);

        $payment->setSubscription($subscription);

        $oldSubscription->setStatus(SubscriptionStatus::CANCELED);

        $this->entityManager->persist($payment);
        $this->entityManager->persist($subscription);

        $this->entityManager->flush();
    }
}
