<?php

namespace App\Enums;

enum AiSourceStatus: string
{
    case DRAFT = 'draft';
    case UPLOADED = 'uploaded';
    case PROCESSING = 'processing';
    case INDEXED = 'indexed';
    case READY = 'ready';
    case ACTIVE = 'active';
    case FAILED = 'failed';
}
