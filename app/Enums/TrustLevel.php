<?php

namespace App\Enums;

enum TrustLevel: string
{
    case GENERAL = 'general';
    case INTERNAL = 'internal';
    case VERIFIED = 'verified';
}
