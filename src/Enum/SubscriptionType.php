<?php

namespace App\Enum;

enum SubscriptionType: string
{
    case FREE = 'free';
    case PRO = 'pro';
    case BUSINESS = 'business';
}
