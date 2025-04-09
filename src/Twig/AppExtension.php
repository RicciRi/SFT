<?php

// src/Twig/AppExtension.php

namespace App\Twig;

use App\Entity\Company;
use App\Enum\SubscriptionType;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private Security $security;

    public function __construct(
        Security $security,
    ) {
        $this->security = $security;
    }

    public function getGlobals(): array
    {
        $user = $this->security->getUser();

        if (!$user) {
            return [];
        }

        $liceseType = $this->getCompany()->getActiveSubscription()->getType();
        $proType = SubscriptionType::PRO->value;
        $businessType = SubscriptionType::BUSINESS->value;
        $freeType = SubscriptionType::FREE->value;

        return [
            'company_data' => $this->getCompany(),
            'licenseType' => $liceseType->value,
            'freeType' => $freeType,
            'proType' => $proType,
            'businessType' => $businessType,
        ];
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

    private function getCompany(): ?Company
    {
        $user = $this->security->getUser();

        if (!$user || !method_exists($user, 'getCompany')) {
            return null;
        }

        return $user->getCompany();
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('highlight', [$this, 'highlight'], ['is_safe' => ['html']]),
        ];
    }

    public function highlight(string $text, ?string $term): string
    {
        if (!$term || '' === trim($term)) {
            return htmlspecialchars($text);
        }

        return preg_replace(
            '/('.preg_quote($term, '/').')/i',
            '<mark>$1</mark>',
            htmlspecialchars($text)
        );
    }
}
