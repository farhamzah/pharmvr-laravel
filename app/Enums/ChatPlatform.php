<?php

namespace App\Enums;

enum ChatPlatform: string
{
    case MOBILE = 'mobile';
    case WEB = 'web';
    case VR = 'vr';
    case ADMIN = 'admin';
}
