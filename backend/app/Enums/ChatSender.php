<?php

namespace App\Enums;

enum ChatSender: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case SYSTEM = 'system';
}
