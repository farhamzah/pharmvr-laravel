<?php

namespace App\Enums;

enum QuestionUsageScope: string
{
    case PRETEST = 'pretest';
    case POSTTEST = 'posttest';
    case BOTH = 'both';
}
