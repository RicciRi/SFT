<?php

namespace App\Enum;

enum FileStatus: string
{
    case UPLOADED = 'uploaded';
    case DOWNLOADED = 'downloaded';
    case EXPIRED = 'expired';
    case DELETED = 'deleted';
}
