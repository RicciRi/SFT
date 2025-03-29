<?php

namespace App\Enum;

enum SubscriptionPeriod: string
{
    case MONTHLY = 'monthly'; // 1 m
    case SEMI_ANNUAL = 'semi_annual'; // 6 m
    case YEARLY = 'yearly'; // 12 m
    case LIFETIME = 'lifetime'; // forever
}
