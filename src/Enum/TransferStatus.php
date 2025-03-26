<?php

namespace App\Enum;

enum TransferStatus: string
{
    case UPLOADED = 'uploaded';
    case DOWNLOADED = 'downloaded';
}
