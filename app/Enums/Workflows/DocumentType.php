<?php

namespace App\Enums\Workflows;

enum DocumentType: int
{
    case LEGISLATION = 1;
    case LEGAL_UPDATE = 2;
    case GENERIC = 3;
    case SOURCE_MONITORING = 4;
}
