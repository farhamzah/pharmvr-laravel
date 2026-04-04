<?php

namespace App\Enums;

enum SourceType: string
{
    case PDF = 'pdf';
    case DOCX = 'docx';
    case TXT = 'txt';
    case MD = 'md';
    case MANUAL = 'manual';
    case WEB = 'web';
}
