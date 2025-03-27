<?php

namespace App\Enum;

enum UserRoles: string
{
    case ROLE_USER = 'ROLE_USER';
    case ROLE_COMPANY_EMPLOYEE = 'ROLE_COMPANY_EMPLOYEE';
    case ROLE_COMPANY_ADMIN = 'ROLE_COMPANY_ADMIN';
    case ROLE_GLOBAL_ADMIN = 'ROLE_GLOBAL_ADMIN';
}
