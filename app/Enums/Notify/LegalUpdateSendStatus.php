<?php

namespace App\Enums\Notify;

enum LegalUpdateSendStatus: int
{
    case PENDING = 0;
    case WAITING = 1;
    case READY = 2;
}
