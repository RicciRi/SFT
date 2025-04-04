<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isFree', [$this, 'isFree']),
            new TwigFunction('isPro', [$this, 'isPro']),
            new TwigFunction('isBusiness', [$this, 'isBusiness']),
        ];
    }

    public function isFree(): bool
    {
        $type = $this->getCompany()->getActiveSubscription()->getType();

        return 'free' === $type->value;
    }

    public function isPro(): bool
    {
        $type = $this->getCompany()->getActiveSubscription()->getType();

        return 'pro' === $type->value;
    }

    public function isBusiness(): bool
    {
        $type = $this->getCompany()->getActiveSubscription()->getType();

        return 'business' === $type->value;
    }

    private function getCompany(): ?\App\Entity\Company
    {
        $user = $this->security->getUser();

        if (!$user || !method_exists($user, 'getCompany')) {
            return null;
        }

        return $user->getCompany();
    }
}
