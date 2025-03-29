<?php

namespace App\Enum;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case TRIALING = 'trialing';
    case CANCELED = 'canceled';
    case SUSPENDED = 'suspended';
    case FAILED = 'failed';
}
