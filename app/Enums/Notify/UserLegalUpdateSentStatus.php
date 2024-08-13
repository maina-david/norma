<?php

namespace App\Enums\Notify;

enum UserLegalUpdateSentStatus: int
{
    case WAITING = 0;
    case SENT = 1;
    case ERROR = 2;
}
