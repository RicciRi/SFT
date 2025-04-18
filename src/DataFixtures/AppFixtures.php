<?php

namespace App\DataFixtures;

use App\Enum\FileStatus;
use App\Enum\TransferStatus;
use App\Factory\CompanyFactory;
use App\Factory\FileDownloadLogFactory;
use App\Factory\FileTransferFactory;
use App\Factory\PaymentFactory;
use App\Factory\SubscriptionFactory;
use App\Factory\TransferredFileFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

// use Psr\Log\LoggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        // private LoggerInterface $logger,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (CompanyFactory::createMany(1) as $company) {
            $mainUser = UserFactory::createOne([
                'isMainAccount' => true,
                'company' => $company,
                'roles' => ['ROLE_COMPANY_ADMIN'],
            ]);

            $subscription = SubscriptionFactory::createOne(['company' => $company]);
            $payment = PaymentFactory::createOne(['subscription' => $subscription]);

            $subscription->addPayment($payment->_real());
            $company->setMainUser($mainUser->_real());
            $company->addSubscription($subscription->_real());

            $transferCount = 4;

            foreach (UserFactory::createMany(5, ['company' => $company]) as $user) {
                foreach (FileTransferFactory::createMany($transferCount, ['user' => $user, 'company' => $company]) as $transfer) {
                    $company->addFileTransfer($transfer->_real());

                    foreach (TransferredFileFactory::createMany(2, ['fileTransfer' => $transfer]) as $file) {
                        $transfer->addTransferredFile($file->_real());
                    }
                }
                foreach (FileTransferFactory::createMany($transferCount, ['user' => $user, 'company' => $company, 'status' => TransferStatus::DOWNLOADED]) as $transfer) {
                    $company->addFileTransfer($transfer->_real());

                    foreach (TransferredFileFactory::createMany(2, ['fileTransfer' => $transfer, 'status' => FileStatus::DOWNLOADED]) as $file) {
                        $transfer->addTransferredFile($file->_real());
                        FileDownloadLogFactory::createOne([
                            'downloadedBy' => $user,
                            'file' => $file,
                            'downloadedAt' => new \DateTimeImmutable(),
                        ]);
                    }
                }

                //                $transferCount = $transferCount + 10;
            }
        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
